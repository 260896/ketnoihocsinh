<?php
/* QT07 – Cấu hình đầu mối & vận hành (chỉ Quản trị viên) */
require __DIR__ . '/../includes/bootstrap.php';
$me = require_staff(true);

$cfg = config();
$staff = array_column(staff_all(), 'name', 'id');
$dayPresets = ['1-5' => 'Thứ 2 – Thứ 6', '1-6' => 'Thứ 2 – Thứ 7', '0-6' => 'Cả tuần'];
$filePresets = ['pdf,jpg,jpeg,png,mp4' => 'PDF, JPG, JPEG, PNG, MP4', 'pdf,jpg,jpeg,png' => 'PDF, JPG, JPEG, PNG', 'jpg,jpeg,png' => 'JPG, PNG'];

if (is_post()) {
    $a = post('action');

    if ($a === 'save') {
        // Dùng chỉ số (0,1,2…) làm khóa vì tên nhóm có thể chứa ký tự [ ]
        foreach ([...issue_groups(), ISSUE_UNKNOWN] as $gi => $g) {
            $r = $_POST['routing'][$gi] ?? [];
            $pick = fn ($k) => isset($staff[$r[$k] ?? '']) ? $r[$k] : '';
            $cfg['routing'][$g] = ['primary' => $pick('primary'), 'backup' => $pick('backup'), 'independent' => $pick('independent')];
        }
        $time = fn ($v, $d) => preg_match('/^\d{2}:\d{2}$/', $v) ? $v : $d;
        $cfg['duty_start'] = $time(post('duty_start'), $cfg['duty_start']);
        $cfg['duty_end'] = $time(post('duty_end'), $cfg['duty_end']);
        if (isset($dayPresets[post('duty_days')])) {
            [$from, $to] = array_map('intval', explode('-', post('duty_days')));
            $cfg['duty_days'] = range($from, $to);
        }
        $cfg['urgent_ack_minutes'] = max(1, (int) post('urgent_ack_minutes', '15'));
        $cfg['first_reply_days'] = max(1, (int) post('first_reply_days', '2'));
        $cfg['max_files'] = min(20, max(1, (int) post('max_files', '5')));
        $cfg['fallback'] = !empty($_POST['fallback']);
        $cfg['hotline'] = post('hotline');
        if (isset($filePresets[post('file_types')])) $cfg['file_types'] = explode(',', post('file_types'));
        $cfg['max_file_mb'] = min(50, max(1, (int) post('max_file_mb', '10')));
        unset($cfg['join_code']); // mã chung cũ – nay dùng danh sách mã trong qt/ma-tham-gia.php
        flash('Đã lưu cấu hình.');
        config_save($cfg);
        audit_log('Cấu hình', null, 'Cập nhật cấu hình vận hành', $me);
        redirect('qt/cau-hinh.php');
    }

    if ($a === 'tpl_add' || $a === 'tpl_edit') {
        if (post('name') === '' || post('body') === '') {
            flash('Nhập tên và nội dung mẫu trả lời.', 'error');
            redirect('qt/cau-hinh.php');
        }
        if ($a === 'tpl_add') {
            $cfg['templates'][] = ['id' => new_id('t'), 'name' => post('name'), 'body' => post('body')];
        } else {
            foreach ($cfg['templates'] as &$tpl) {
                if ($tpl['id'] === post('id')) $tpl = ['id' => $tpl['id'], 'name' => post('name'), 'body' => post('body')];
            }
            unset($tpl);
        }
        config_save($cfg);
        flash('Đã lưu mẫu trả lời.');
        redirect('qt/cau-hinh.php');
    }
    if ($a === 'tpl_delete') {
        $cfg['templates'] = array_values(array_filter($cfg['templates'], fn ($t) => $t['id'] !== post('id')));
        config_save($cfg);
        flash('Đã xóa mẫu trả lời.');
        redirect('qt/cau-hinh.php');
    }
}

$times = [];
for ($m = 0; $m < 1440; $m += 30) $times[] = sprintf('%02d:%02d', intdiv($m, 60), $m % 60);
$daysKey = $cfg['duty_days'] === range(1, 6) ? '1-6' : (count($cfg['duty_days']) === 7 ? '0-6' : '1-5');
$filesKey = implode(',', $cfg['file_types']);
$F = ['form' => 'cfg'];

admin_start('Cấu hình đầu mối', 'Cấu hình đầu mối & vận hành', 'Phiếu tự chuyển theo nhóm vấn đề. Phiếu chưa rõ nhóm vào hàng chờ phân loại.',
    '<button type="submit" form="cfg" name="action" value="save" class="btn">Lưu cấu hình</button>');
?>
<form method="post" id="cfg"><?= csrf_field() ?></form>

<div class="a-cols">
  <div class="a-col-main">
    <div class="card card--admin">
      <div>
        <p class="t-h2">Bảng đầu mối theo nhóm vấn đề</p>
        <p class="t-small t-muted">Đầu mối độc lập nhận phiếu khi phiếu liên quan đến chính đầu mối hoặc người dự phòng.</p>
      </div>
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th scope="col" style="width:160px">Nhóm vấn đề</th><th scope="col">Đầu mối</th><th scope="col">Người dự phòng</th><th scope="col">Đầu mối độc lập</th></tr></thead>
          <tbody>
          <?php foreach ([...issue_groups(), ISSUE_UNKNOWN] as $i => $g): $r = $cfg['routing'][$g] ?? []; ?>
            <tr>
              <td><?= e($g) ?></td>
              <?php foreach (['primary' => 'Đầu mối', 'backup' => 'Người dự phòng', 'independent' => 'Đầu mối độc lập'] as $k => $lbl):
                  $opts = ($k === 'primary' && $g === ISSUE_UNKNOWN) ? ['' => 'Hàng chờ phân loại'] + $staff : ['' => '— Chưa chọn —'] + $staff; ?>
                <td><?= field_select("routing[$i][$k]", "$lbl – $g", $opts, (string) ($r[$k] ?? ''), $F + ['id' => "r$i-$k", 'sr' => true]) ?></td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="card card--admin">
      <p class="t-h2">Mẫu trả lời</p>
      <?php foreach ($cfg['templates'] as $tpl): ?>
        <details class="disclosure panel" style="border-radius:10px;padding:6px 14px">
          <summary class="row gap-12" style="min-height:44px">
            <span class="t-small t-strong grow"><?= e($tpl['name']) ?></span>
            <span class="icon-btn" aria-hidden="true"><?= icon('edit', 18, 'var(--c-primary)') ?></span>
            <span class="sr-only">Sửa mẫu <?= e($tpl['name']) ?></span>
          </summary>
          <form method="post" class="stack gap-10" style="padding:6px 0 10px">
            <?= csrf_field() ?><input type="hidden" name="action" value="tpl_edit"><input type="hidden" name="id" value="<?= e($tpl['id']) ?>">
            <?= field_input('name', 'Tên mẫu', $tpl['name'], ['id' => 'tn-' . $tpl['id']]) ?>
            <?= field_textarea('body', 'Nội dung', $tpl['body'], ['id' => 'tb-' . $tpl['id'], 'rows' => 3]) ?>
            <div class="row gap-10"><button type="submit" class="btn btn--sm">Lưu mẫu</button></div>
          </form>
          <div style="padding-bottom:10px"><?= post_button('Xóa mẫu này', ['action' => 'tpl_delete', 'id' => $tpl['id']], 'link link--danger') ?></div>
        </details>
      <?php endforeach; ?>
      <details class="disclosure">
        <summary class="btn btn--outline">Thêm mẫu trả lời</summary>
        <form method="post" class="stack gap-10" style="margin-top:12px">
          <?= csrf_field() ?><input type="hidden" name="action" value="tpl_add">
          <?= field_input('name', 'Tên mẫu *', '', ['id' => 'tn-new']) ?>
          <?= field_textarea('body', 'Nội dung *', '', ['id' => 'tb-new', 'rows' => 3]) ?>
          <div><button type="submit" class="btn btn--sm">Thêm mẫu</button></div>
        </form>
      </details>
    </div>
  </div>

  <div class="a-col-side" style="flex-basis:380px">
    <div class="card card--admin">
      <p class="t-h2">Giờ trực &amp; thời hạn</p>
      <div class="filter-grid" style="grid-template-columns:repeat(auto-fit,minmax(120px,1fr))">
        <?= field_select('duty_start', 'Bắt đầu trực', $times, $cfg['duty_start'], $F) ?>
        <?= field_select('duty_end', 'Kết thúc trực', $times, $cfg['duty_end'], $F) ?>
      </div>
      <?= field_select('duty_days', 'Ngày trực', $dayPresets, $daysKey, $F) ?>
      <?= field_input('urgent_ack_minutes', 'Hạn xác nhận phiếu khẩn (phút)', (string) $cfg['urgent_ack_minutes'], $F + ['type' => 'number', 'min' => 1]) ?>
      <?= field_input('first_reply_days', 'Hạn phản hồi lần đầu – phản ánh thường (ngày làm việc)', (string) ($cfg['first_reply_days'] ?? 2), $F + ['type' => 'number', 'min' => 1]) ?>
      <div class="row gap-12">
        <label for="f-fallback" class="grow" style="cursor:pointer"><span class="t-small t-strong d-block">Chuyển người dự phòng khi quá hạn</span><span class="t-small t-muted">Nếu chưa có ai xác nhận</span></label>
        <input type="checkbox" role="switch" class="switch" id="f-fallback" name="fallback" value="1" form="cfg" <?= $cfg['fallback'] ? 'checked' : '' ?>>
      </div>
      <?= field_input('hotline', 'Số hỗ trợ khẩn cấp ngoài giờ *', $cfg['hotline'], $F + ['type' => 'tel', 'placeholder' => SITE['hotline']]) ?>
    </div>

    <div class="card card--admin">
      <p class="t-h2">Tệp đính kèm</p>
      <p class="t-small t-muted">Mã tham gia cho học sinh được nạp và quản lý ở trang <a href="<?= e(url('qt/ma-tham-gia.php')) ?>" class="link">Mã tham gia</a>.</p>
      <?= field_select('file_types', 'Loại tệp cho phép', $filePresets, $filesKey, $F) ?>
      <?= field_input('max_file_mb', 'Dung lượng tối đa mỗi tệp (MB)', (string) $cfg['max_file_mb'], $F + ['type' => 'number', 'min' => 1, 'max' => 50]) ?>
      <?= field_input('max_files', 'Số tệp tối đa mỗi phản ánh', (string) ($cfg['max_files'] ?? 5), $F + ['type' => 'number', 'min' => 1, 'max' => 20]) ?>
      <p class="t-small t-muted">Giới hạn thật còn phụ thuộc <code>upload_max_filesize</code> trong php.ini (hiện tại: <?= e(ini_get('upload_max_filesize')) ?>).</p>
    </div>
  </div>
</div>
<?php admin_end();
