<?php
/* SV05 – Quên mật khẩu */
require __DIR__ . '/../includes/bootstrap.php';

$sent = false;
$demoLink = null;
$error = null;
if (is_post()) {
    $key = 'reset|' . client_ip();
    if ($w = throttle_wait($key, THROTTLE_MAX_IP)) {
        $error = throttle_message($w);
    } else {
        throttle_fail($key); // giới hạn số lần yêu cầu đặt lại từ một máy
        $sent = true;
        // Luôn báo cùng một thông điệp để không lộ email nào đã đăng ký.
        if ($u = user_find_by_email(post('email'))) {
            $u['reset_token'] = bin2hex(random_bytes(16));
            $u['reset_expires'] = time() + 3600;
            user_save($u);
            $link = url('sv/mat-khau-moi.php', ['token' => $u['reset_token']]);
            mail_send($u['email'], 'Đặt lại mật khẩu – ' . SITE['project_name'],
                "Có yêu cầu đặt lại mật khẩu cho tài khoản của bạn.\nMở liên kết (hiệu lực 60 phút): " . site_link(ltrim(substr($link, strlen(BASE_URL)), '/')) .
                "\nNếu bạn không yêu cầu, hãy bỏ qua thư này.");
            // CHỈ bản chạy thử và CHỈ tài khoản học sinh: hiện liên kết trên trang vì chưa gửi email thật.
            // Không bao giờ hiện với tài khoản cán bộ (tránh chiếm quyền quản trị).
            if (SITE['is_demo'] && $u['role'] === 'student' && empty(config()['mail_enabled'])) $demoLink = $link;
        }
    }
}

auth_start('Quên mật khẩu', ['heading' => 'Đặt lại mật khẩu', 'back' => url('sv/dang-nhap.php'), 'back_label' => 'Đăng nhập',
    'subtitle' => 'Nhập email đã đăng ký. Chúng tôi sẽ gửi liên kết đặt lại mật khẩu.']);
?>
<form method="post" class="auth-form">
  <?= csrf_field() ?>
  <?= field_input('email', 'Email', post('email'), ['type' => 'email', 'placeholder' => 'ten@email.com', 'autocomplete' => 'email', 'required' => true, 'error' => $error]) ?>
  <button type="submit" class="btn btn--block">Gửi liên kết đặt lại</button>
  <?php if ($sent): ?>
    <div class="panel panel--info" role="status"><?= note('check', 'Nếu email có trong hệ thống, liên kết đặt lại đã được gửi. Kiểm tra cả thư rác.') ?></div>
    <?php if ($demoLink): ?>
      <div class="demo-box"><strong>Bản chạy thử:</strong> chưa gửi email thật. <a href="<?= e($demoLink) ?>">Mở liên kết đặt lại</a>.</div>
    <?php endif; ?>
  <?php endif; ?>
</form>
<?php auth_end();
