<?php
/* SV12–SV16 – Gửi phiếu 5 bước:  gui-phieu.php?buoc=1..5
 * Bản nháp lưu trong session cho tới khi bấm "Xác nhận gửi". */
require __DIR__ . '/../includes/bootstrap.php';
$me = require_student();

$step = (int) get('buoc', '0');
$draft = $_SESSION['draft'] ?? [
    'type' => '', 'group' => '', 'urgent' => '', 'description' => '',
    'occurred_at' => '', 'location' => '', 'files' => [], 'anonymous' => '1',
];
$cfg = config();
$groups = issue_groups();

// Vào luồng: ngoài giờ trực thì hiện SV18 trước
if ($step === 0) {
    redirect(is_out_of_hours() ? 'sv/ngoai-gio-truc.php' : 'sv/gui-phieu.php', is_out_of_hours() ? [] : ['buoc' => 1]);
}

// Chặn nhảy cóc bước
$need = [2 => 'type', 3 => 'group', 4 => 'urgent', 5 => 'description'];
for ($s = 2; $s <= min($step, 5); $s++) {
    if (($draft[$need[$s]] ?? '') === '') redirect('sv/gui-phieu.php', ['buoc' => $s - 1]);
}

$error = null;
if (is_post()) {
    $go = fn (int $n) => redirect('sv/gui-phieu.php', ['buoc' => $n]);
    switch ($step) {
        case 1:
            if (isset(REQUEST_TYPES[post('type')])) { $draft['type'] = post('type'); $_SESSION['draft'] = $draft; $go(2); }
            $error = 'Chọn một loại yêu cầu.';
            break;
        case 2:
            if (in_array(post('group'), [...$groups, ISSUE_UNKNOWN], true)) { $draft['group'] = post('group'); $_SESSION['draft'] = $draft; $go(3); }
            $error = 'Chọn một nhóm vấn đề.';
            break;
        case 3:
            if (in_array(post('urgent'), ['1', '0'], true)) {
                $draft['urgent'] = post('urgent');
                $_SESSION['draft'] = $draft;
                // Chọn "Có" lần đầu: ở lại để hiện số khẩn cấp; bấm Tiếp tục lần nữa mới sang bước 4
                if (post('urgent') === '1' && post('confirm') !== '1') redirect('sv/gui-phieu.php', ['buoc' => 3]);
                $go(4);
            }
            $error = 'Chọn Có hoặc Không.';
            break;
        case 4:
            $draft['description'] = post('description');
            $draft['occurred_at'] = post('occurred_at');
            $draft['location'] = post('location');
            if (post('remove') !== '') {
                $draft['files'] = array_values(array_filter($draft['files'], fn ($f) => $f['path'] !== post('remove')));
            }
            $maxFiles = (int) ($cfg['max_files'] ?? 5);
            foreach (files_list('files') as $f) {
                if (count($draft['files']) >= $maxFiles) {
                    $error = 'Mỗi phản ánh được đính kèm tối đa ' . $maxFiles . ' tệp.';
                    break;
                }
                $r = save_ticket_upload($f, $cfg['file_types'], (int) $cfg['max_file_mb']);
                if (is_string($r)) $error = $r; else $draft['files'][] = $r;
            }
            $_SESSION['draft'] = $draft;
            if (post('action') === 'next' && !$error) {
                if ($draft['description'] === '') { $error = 'Hãy mô tả sự việc.'; break; }
                $go(5);
            }
            if (!$error) redirect('sv/gui-phieu.php', ['buoc' => 4]);
            break;
        case 5:
            $draft['anonymous'] = post('anonymous') === '0' ? '0' : '1';
            $ticket = ticket_create($me, $draft);
            unset($_SESSION['draft']);
            redirect('sv/gui-thanh-cong.php', ['id' => $ticket['code']]);
    }
}

$footer = function (int $back = 0, string $label = 'Tiếp tục', array $btn = []) {
    mobile_footer();
    if ($back) echo '<a href="' . e(url('sv/gui-phieu.php', ['buoc' => $back])) . '" class="btn btn--outline">Quay lại</a>';
    echo '<button type="submit" form="step" class="btn"' . attrs($btn) . '>' . e($label) . '</button>';
};

$backTop = $step === 1 ? url('sv/hoc.php') : url('sv/gui-phieu.php', ['buoc' => $step - 1]);
mobile_start('Gửi phiếu', ['back' => $backTop]);
echo step_progress($step);
if ($error) echo '<div class="field__error">' . icon('alert', 16, 'var(--c-danger)') . '<p>' . e($error) . '</p></div>';

/* ---------------- Bước 1 ---------------- */
if ($step === 1): ?>
  <h2 class="t-h1">Bạn muốn gửi loại yêu cầu nào?</h2>
  <form method="post" id="step" class="stack gap-16">
    <?= csrf_field() ?>
    <?php foreach (REQUEST_TYPES as $t => $hint) echo choice_radio('type', $t, $t, $hint, $draft['type'] === $t); ?>
  </form>
  <?= emergency_card() ?>
  <?php $footer(); mobile_end('Gửi phiếu'); exit;

/* ---------------- Bước 2 ---------------- */
elseif ($step === 2): ?>
  <h2 class="t-h1">Vấn đề thuộc nhóm nào?</h2>
  <p class="t-small t-muted">Danh sách cố định theo kịch bản đã chốt với khách hàng.</p>
  <form method="post" id="step" class="stack gap-16">
    <?= csrf_field() ?>
    <?php foreach ($groups as $g) echo choice_radio('group', $g, $g, '', $draft['group'] === $g); ?>
    <?= choice_radio('group', ISSUE_UNKNOWN, ISSUE_UNKNOWN, 'Cán bộ sẽ phân loại giúp bạn', $draft['group'] === ISSUE_UNKNOWN) ?>
  </form>
  <?php $footer(1); mobile_end(); exit;

/* ---------------- Bước 3 ---------------- */
elseif ($step === 3): ?>
  <h2 class="t-h1">Bạn có cần hỗ trợ ngay không?</h2>
  <form method="post" id="step" class="stack gap-16">
    <?= csrf_field() ?>
    <?php if ($draft['urgent'] === '1'): ?><input type="hidden" name="confirm" value="1"><?php endif; ?>
    <?= choice_radio('urgent', '1', 'Có, tôi cần hỗ trợ ngay', '', $draft['urgent'] === '1') ?>
    <?= choice_radio('urgent', '0', 'Không, có thể chờ phản hồi', '', $draft['urgent'] === '0') ?>
  </form>
  <?php if ($draft['urgent'] === '1'): ?>
    <div class="panel panel--danger-bordered" style="border-radius:16px;padding:18px;gap:12px" role="alert">
      <div class="row gap-10">
        <span class="icon-circle" style="width:40px;height:40px;background:var(--c-danger)"><?= icon('phone', 20, '#fff') ?></span>
        <p class="t-h2 t-danger">Gọi ngay để được hỗ trợ</p>
      </div>
      <p>Nếu bạn đang gặp nguy hiểm, đừng chờ phiếu được xử lý.</p>
      <a href="<?= e(tel_href()) ?>" class="btn btn--danger btn--block">Gọi <?= e(hotline()) ?></a>
      <p class="t-small t-muted">Bạn vẫn có thể gửi phiếu. Phiếu sẽ được đánh dấu khẩn và gửi email ngay cho cán bộ trực.</p>
    </div>
  <?php endif; ?>
  <?php $footer(2); mobile_end(); exit;

/* ---------------- Bước 4 ---------------- */
elseif ($step === 4): ?>
  <h2 class="t-h1">Mô tả sự việc</h2>
  <form method="post" id="step" enctype="multipart/form-data" class="stack gap-16">
    <?= csrf_field() ?>
    <?= field_textarea('description', 'Mô tả *', $draft['description'], ['placeholder' => 'Chuyện gì đã xảy ra? Ai liên quan? (không bắt buộc ghi tên)', 'required' => true]) ?>
    <?= field_input('occurred_at', 'Thời gian (nếu nhớ)', $draft['occurred_at'], ['type' => 'datetime-local']) ?>
    <?= field_input('location', 'Địa điểm', $draft['location'], ['placeholder' => 'Ví dụ: khu B, nhóm chat lớp…']) ?>
    <p class="t-small t-strong">Tệp đính kèm</p>
    <?= dropzone('files[]', 'Chọn tệp để tải lên', strtoupper(implode(', ', $cfg['file_types'])) . ' · tối đa ' . $cfg['max_file_mb'] . ' MB/tệp, ' . ($cfg['max_files'] ?? 5) . ' tệp/phản ánh (đã có ' . count($draft['files']) . ')', ['multiple' => true, 'accept' => '.' . implode(',.', $cfg['file_types']), 'icon' => 24]) ?>
    <button type="submit" name="action" value="upload" class="btn btn--outline btn--sm self-start"><?= icon('upload', 16) ?> Tải tệp đã chọn lên</button>
    <?php if ($draft['files']): ?>
      <div class="card" style="border-radius:12px;padding:8px 12px">
        <?php foreach ($draft['files'] as $f): ?>
          <div class="row gap-12">
            <?= icon('file', 20, 'var(--c-primary)') ?>
            <span class="grow"><span class="t-small t-strong d-block" style="word-break:break-all"><?= e($f['name']) ?></span><span class="t-small t-muted"><?= e($f['size']) ?> · Đã tải lên<?= !empty($f['clean_path']) ? ' · Đã xóa vị trí GPS, thông tin máy chụp' : '' ?></span></span>
            <button type="submit" name="remove" value="<?= e($f['path']) ?>" class="icon-btn" aria-label="Xóa tệp <?= e($f['name']) ?>"><?= icon('x', 18, 'var(--c-muted)') ?></button>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </form>
  <?php $footer(3, 'Xem lại', ['name' => 'action', 'value' => 'next']); mobile_end(); exit;

/* ---------------- Bước 5 ---------------- */
else:
    $rows = [
        'Loại yêu cầu'         => $draft['type'],
        'Nhóm vấn đề'          => $draft['group'],
        'Cần hỗ trợ ngay'      => $draft['urgent'] === '1' ? 'Có – phiếu khẩn' : 'Không',
        'Mô tả'                => $draft['description'],
        'Thời gian · Địa điểm' => fmt_dt($draft['occurred_at'] ?: null) . ' · ' . ($draft['location'] ?: '—'),
        'Tệp'                  => $draft['files'] ? count($draft['files']) . ' tệp' : 'Không có',
    ]; ?>
  <h2 class="t-h1">Xem lại trước khi gửi</h2>
  <div class="card" style="gap:12px">
    <?php foreach ($rows as $k => $v): ?>
      <div class="row gap-12 items-start"><p class="t-small t-muted" style="flex:0 0 120px"><?= e($k) ?></p><p class="t-small t-strong pre" style="min-width:0"><?= e($v) ?></p></div>
    <?php endforeach; ?>
    <a href="<?= e(url('sv/gui-phieu.php', ['buoc' => 4])) ?>" class="link link--lg">Sửa thông tin</a>
  </div>
  <p class="t-h2">Gửi với danh tính?</p>
  <form method="post" id="step" class="stack gap-16">
    <?= csrf_field() ?>
    <?= choice_radio('anonymous', '1', 'Ẩn danh', 'Cán bộ xử lý không thấy tên và email của bạn', $draft['anonymous'] === '1') ?>
    <?= choice_radio('anonymous', '0', 'Có danh tính', 'Cán bộ thấy tên hiển thị và email để liên hệ', $draft['anonymous'] === '0') ?>
  </form>
  <div class="panel" style="border-radius:16px;padding:16px">
    <p class="t-title t-primary">Ai xem được phiếu này?</p>
    <?= note('users', 'Cán bộ phụ trách nhóm vấn đề đọc nội dung và trả lời bạn.') ?>
    <?= note('lock', 'Chỉ người được chỉ định mới mở được danh tính. Mỗi lần mở đều ghi nhật ký.') ?>
    <?= note('eyeOff', 'Ẩn danh là ẩn khi hiển thị. Hệ thống vẫn lưu liên kết với tài khoản ở nơi riêng.') ?>
    <?= note('file', 'Tệp gốc (vị trí GPS, thiết bị, thời điểm chụp) chỉ cán bộ có thẩm quyền xem để xác minh.') ?>
  </div>
  <?php $footer(4, 'Xác nhận gửi'); mobile_end(); exit;
endif;
