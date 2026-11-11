<?php
/* SV20 – Chi tiết phiếu & hộp thư */
require __DIR__ . '/../includes/bootstrap.php';
$me = require_student();

$t = ticket_find(get('id'));
if (!$t || !ticket_is_owned_by($t, $me)) redirect('sv/phieu.php');

if (is_post()) {
    $text = post('msg');
    $file = files_list('file')[0] ?? null;
    $cfg = config();
    $attach = null;
    if ($file && count($t['attachments']) >= (int) ($cfg['max_files'] ?? 5)) {
        flash('Mỗi phản ánh được đính kèm tối đa ' . ($cfg['max_files'] ?? 5) . ' tệp.', 'error');
        redirect_back();
    }
    if ($file) {
        $r = save_ticket_upload($file, $cfg['file_types'], (int) $cfg['max_file_mb']);
        if (is_string($r)) { flash($r, 'error'); redirect_back(); }
        $t['attachments'][] = $r;
        $attach = count($t['attachments']) - 1;
    }
    if ($text !== '' || $attach !== null) {
        // Không ghi id tài khoản vào tin nhắn (liên kết người gửi lưu riêng)
        $t['messages'][] = ['from' => 'student', 'time' => now_iso(), 'text' => $text, 'attachment' => $attach];
        array_unshift($t['history'], ['time' => now_iso(), 'text' => 'Học sinh gửi tin nhắn']);
        ticket_save($t);
        notify_staff($t['assignee'] ? [$t['assignee']] : routing_people(ISSUE_UNKNOWN), $t, 'Học sinh trả lời phiếu');
    }
    redirect_back();
}

// Đánh dấu đã xem cập nhật
if ($t['has_update']) {
    $t['has_update'] = false;
    ticket_save($t);
}

$steps = ['Đã tiếp nhận' => 'Đã tiếp nhận', 'Cần bổ sung' => 'Cần bổ sung', 'Đang xem xét' => 'Đang xem xét', 'Đã có phản hồi' => 'Đã phản hồi'];
$current = array_search($t['status'], array_keys($steps), true);

mobile_start('Phiếu #' . $t['code'], ['back' => url('sv/phieu.php')]);
?>
<div class="card">
  <div class="row gap-8"><?= status_badge($t['status']) ?><span class="badge badge--muted badge--square"><?= $t['anonymous'] ? 'Ẩn danh' : 'Có danh tính' ?></span></div>
  <p class="t-small t-muted"><?= e($t['type']) ?> · <?= e($t['group']) ?> · Gửi <?= e(fmt_dt($t['created_at'])) ?></p>
  <ol aria-label="Tiến trình phiếu" class="row gap-4" style="list-style:none;margin:0;padding:0;align-items:stretch">
    <?php $i = 0; foreach ($steps as $s => $label):
        $reached = $i <= $current;
        $color = !$reached ? 'var(--c-neutral)' : ($s === 'Cần bổ sung' && $i === $current ? 'var(--c-danger)' : 'var(--c-primary)'); ?>
      <li class="stack gap-6" style="flex:1 1 0"<?= $i === $current ? ' aria-current="step"' : '' ?>>
        <span style="height:4px;border-radius:2px;background:<?= $color ?>"></span>
        <span class="t-small" style="color:<?= $reached ? $color : 'var(--c-muted)' ?>"><?= e($label) ?></span>
      </li>
    <?php $i++; endforeach; ?>
  </ol>
</div>

<?php if ($t['status'] === 'Cần bổ sung'): ?>
  <div class="panel panel--danger" style="border-radius:16px;padding:16px;gap:6px">
    <div class="row gap-8"><?= icon('alert', 18, 'var(--c-danger)') ?><p class="t-title t-danger">Cán bộ cần bạn bổ sung</p></div>
    <p class="t-small">Trả lời trong hộp thư bên dưới.<?= $t['anonymous'] ? ' Danh tính của bạn vẫn được ẩn.' : '' ?></p>
  </div>
<?php endif; ?>

<p class="t-h2">Hộp thư</p>
<?php foreach ($t['messages'] as $m):
    $mine = $m['from'] === 'student'; ?>
  <div class="msg<?= $mine ? ' msg--me' : '' ?>">
    <div class="bubble <?= $m['from'] === 'system' ? 'bubble--system' : ($mine ? 'bubble--me' : 'bubble--other') ?>" style="max-width:300px">
      <?php if (!$mine): ?><p class="bubble__meta"><?= $m['from'] === 'system' ? 'Xác nhận tự động' : 'Cán bộ phụ trách' ?></p><?php endif; ?>
      <?php if ($m['text'] !== ''): ?><p class="pre"><?= e($m['text']) ?></p><?php endif; ?>
      <?php if (isset($m['attachment'], $t['attachments'][$m['attachment']])): $a = $t['attachments'][$m['attachment']]; ?>
        <a href="<?= e(url('tep.php', ['k' => 'att', 't' => $t['code'], 'i' => $m['attachment']])) ?>" class="row gap-6 t-small"><?= icon('paperclip', 14) ?><?= e($a['name']) ?></a>
      <?php endif; ?>
    </div>
    <p class="t-small t-muted"><?= $mine ? 'Bạn · ' : '' ?><?= e(fmt_dt($m['time'])) ?></p>
  </div>
<?php endforeach; ?>
<div id="cuoi"></div>

<?php mobile_footer('m-composer'); ?>
  <form method="post" enctype="multipart/form-data" class="stack gap-6" action="<?= e(url('sv/chi-tiet-phieu.php', ['id' => $t['code']])) ?>#cuoi">
    <?= csrf_field() ?>
    <div class="row gap-8">
      <label class="icon-btn" title="Đính kèm tệp" style="position:relative;overflow:hidden">
        <?= icon('paperclip', 20, 'var(--c-muted)') ?><span class="sr-only">Đính kèm tệp</span>
        <?php $cf = config(); ?>
        <input type="file" name="file" accept=".<?= e(implode(',.', $cf['file_types'])) ?>" data-exts="<?= e(implode(',', $cf['file_types'])) ?>" data-max-mb="<?= e($cf['max_file_mb']) ?>" data-max-files="<?= e($cf['max_files'] ?? 5) ?>" data-have="<?= count($t['attachments']) ?>" style="position:absolute;inset:0;opacity:0;cursor:pointer">
      </label>
      <label for="msg" class="sr-only">Nhập tin nhắn</label>
      <input id="msg" name="msg" placeholder="Nhập tin nhắn…" class="input grow" style="background:var(--c-bg)" autocomplete="off">
      <button type="submit" class="icon-btn icon-btn--primary" aria-label="Gửi"><?= icon('send', 20, '#fff') ?></button>
    </div>
  </form>
<?php mobile_end();
