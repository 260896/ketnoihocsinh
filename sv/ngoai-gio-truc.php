<?php
/* SV18 – Ngoài giờ trực */
require __DIR__ . '/../includes/bootstrap.php';
require_student();

mobile_start('Gửi phiếu', ['back' => url('sv/hoc.php')]);
?>
<div class="panel panel--dark" style="border-radius:16px;padding:18px">
  <div class="row gap-10"><?= icon('moon', 22, '#fff') ?><p class="t-h2">Hiện đang ngoài giờ trực</p></div>
  <p>Giờ trực: <?= e(duty_label()) ?>. Phản ánh gửi lúc này vẫn được hệ thống ghi nhận và dự kiến được xử lý từ <strong><?= e(expected_processing_label()) ?></strong>.</p>
</div>
<div class="panel panel--danger-bordered" style="border-radius:16px;padding:18px">
  <p class="t-h2 t-danger">Cần hỗ trợ ngay?</p>
  <p>Đừng chờ đến giờ trực. Gọi số hỗ trợ khẩn cấp:</p>
  <a href="<?= e(tel_href()) ?>" class="btn btn--danger btn--block">Gọi <?= e(hotline()) ?></a>
</div>
<p class="t-muted">Bạn vẫn có thể gửi phiếu bây giờ.</p>
<a href="<?= e(url('sv/gui-phieu.php', ['buoc' => 1])) ?>" class="btn btn--block">Tiếp tục gửi phiếu</a>
<?php mobile_end('Gửi phiếu');
