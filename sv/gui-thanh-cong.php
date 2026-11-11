<?php
/* SV17 – Gửi thành công */
require __DIR__ . '/../includes/bootstrap.php';
$me = require_student();

$t = ticket_find(get('id'));
if (!$t || $t['owner_id'] !== $me['id']) redirect('sv/phieu.php');

mobile_start('Đã gửi phiếu');
?>
<div class="stack gap-14 items-center" style="padding-top:24px">
  <span class="icon-circle" style="width:80px;height:80px;background:var(--c-primary)"><?= icon('check', 40, '#fff') ?></span>
  <h2 class="t-h1 t-center">Phiếu đã được gửi</h2>
  <div class="panel panel--info self-stretch" style="border-radius:16px;padding:16px">
    <span class="t-small t-muted">Mã phiếu (lưu lại để tra cứu)</span>
    <span style="font-size:22px;font-weight:700;color:var(--c-primary);user-select:all">#<?= e($t['code']) ?></span>
  </div>
  <div class="card self-stretch" style="gap:6px;align-items:flex-start">
    <span class="badge badge--muted badge--square">Xác nhận tự động</span>
    <p><?= e($t['messages'][0]['text']) ?></p>
  </div>
  <?= note('clock', '<span class="t-muted">' . (is_out_of_hours() ? 'Em gửi ngoài giờ làm việc (' . e(duty_label()) . '). Dự kiến được xử lý từ <strong>' . e(expected_processing_label()) . '</strong>. ' : 'Bộ phận tiếp nhận làm việc ' . e(duty_label()) . '. ') . 'Em nhận email khi phản ánh có cập nhật (không kèm nội dung).</span>', 'var(--c-muted)') ?>
  <a href="<?= e(url('sv/chi-tiet-phieu.php', ['id' => $t['code']])) ?>" class="btn btn--block">Xem phiếu</a>
  <a href="<?= e(url('sv/hoc.php')) ?>" class="btn btn--ghost btn--block">Về trang chủ</a>
</div>
<?php mobile_end('Gửi phiếu');
