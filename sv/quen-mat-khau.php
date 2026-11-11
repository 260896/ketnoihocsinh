<?php
/* SV05 – Quên mật khẩu */
require __DIR__ . '/../includes/bootstrap.php';

$sent = false;
$demoLink = null;
if (is_post()) {
    $sent = true;
    // Luôn báo cùng một thông điệp để không lộ email nào đã đăng ký.
    if ($u = user_find_by_email(post('email'))) {
        $u['reset_token'] = bin2hex(random_bytes(16));
        $u['reset_expires'] = time() + 3600;
        user_save($u);
        $demoLink = url('sv/mat-khau-moi.php', ['token' => $u['reset_token']]);
    }
}

mobile_start('Quên mật khẩu', ['back' => url('sv/dang-nhap.php')]);
?>
<form method="post" class="stack gap-16">
  <?= csrf_field() ?>
  <span class="icon-circle" style="width:56px;height:56px;background:var(--c-primary-50)"><?= icon('key', 28, 'var(--c-primary)') ?></span>
  <h2 class="t-h1">Đặt lại mật khẩu</h2>
  <p class="t-muted">Nhập email đã đăng ký. Chúng tôi sẽ gửi liên kết đặt lại mật khẩu.</p>
  <?= field_input('email', 'Email', post('email'), ['type' => 'email', 'placeholder' => 'ten@email.com', 'autocomplete' => 'email', 'required' => true]) ?>
  <button type="submit" class="btn btn--block">Gửi liên kết đặt lại</button>
  <?php if ($sent): ?>
    <div class="panel panel--info" role="status"><?= note('check', 'Nếu email có trong hệ thống, liên kết đặt lại đã được gửi. Kiểm tra cả thư rác.') ?></div>
    <?php if ($demoLink): ?>
      <div class="demo-box"><strong>Bản chạy thử:</strong> chưa gửi email thật. <a href="<?= e($demoLink) ?>">Mở liên kết đặt lại</a>.</div>
    <?php endif; ?>
  <?php endif; ?>
</form>
<?php mobile_end();
