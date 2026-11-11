<?php
/* SV03 – Xác thực email */
require __DIR__ . '/../includes/bootstrap.php';

// Bấm liên kết xác thực: ?token=...
if (($token = get('token')) !== '') {
    foreach (users_all() as $u) {
        if (($u['verify_token'] ?? '') !== '' && hash_equals($u['verify_token'], $token)) {
            if (($u['verify_expires'] ?? PHP_INT_MAX) < time()) {
                flash('Liên kết xác thực đã hết hạn. Đăng nhập để nhận liên kết mới.', 'error');
                redirect('sv/dang-nhap.php');
            }
            $u['verified'] = true;
            $u['verify_token'] = '';
            user_save($u);
            flash('Đã xác thực email. Bạn có thể đăng nhập.');
            redirect('sv/dang-nhap.php');
        }
    }
    flash('Liên kết xác thực không hợp lệ hoặc đã dùng.', 'error');
    redirect('sv/dang-nhap.php');
}

$email = $_SESSION['pending_email'] ?? '';
$user = $email ? user_find_by_email($email) : null;

// Gửi lại liên kết: giới hạn 60 giây/lần
$wait = max(0, 60 - (time() - ($_SESSION['verify_sent_at'] ?? 0)));
if (is_post() && $user) {
    if ($wait === 0 && !$user['verified']) {
        $user['verify_token'] = bin2hex(random_bytes(16));
        $user['verify_expires'] = time() + 86400;
        user_save($user);
        send_verify_mail($user);
        $_SESSION['verify_sent_at'] = time();
        flash('Đã gửi lại liên kết xác thực.');
    }
    redirect('sv/xac-thuc.php');
}
if (!isset($_SESSION['verify_sent_at'])) {
    $_SESSION['verify_sent_at'] = time();
    $wait = 60;
}

auth_start('Xác thực email', ['heading' => 'Kiểm tra hộp thư của bạn',
    'subtitle' => 'Liên kết xác thực đã gửi tới ' . ($email ?: 'email của bạn') . '. Tài khoản chỉ dùng được sau khi xác thực.']);
?>
  <div class="panel"><?= note('info', 'Không thấy email? Kiểm tra mục Thư rác/Spam. Liên kết có hiệu lực trong ' . e(SITE['verify_ttl']) . '.') ?></div>

  <?php // CHỈ bản chạy thử khi chưa bật gửi thư thật: hiện liên kết để kiểm thử. Bản chính thức: liên kết chỉ có trong email.
  if ($user && !$user['verified'] && SITE['is_demo'] && empty(config()['mail_enabled'])): ?>
    <div class="demo-box">
      <strong>Bản chạy thử:</strong> hệ thống chưa gửi email thật. Bấm vào
      <a href="<?= e(url('sv/xac-thuc.php', ['token' => $user['verify_token']])) ?>">liên kết xác thực này</a>.
    </div>
  <?php endif; ?>

  <form method="post">
    <?= csrf_field() ?>
    <button type="submit" class="btn btn--outline btn--block" <?= $wait > 0 ? 'disabled' : '' ?>>
      <?= $wait > 0 ? 'Gửi lại liên kết (sau ' . $wait . ' giây)' : 'Gửi lại liên kết' ?>
    </button>
  </form>
  <a href="<?= e(url('sv/dang-nhap.php')) ?>" class="btn btn--ghost btn--block">Đã xác thực – Đăng nhập</a>
<?php auth_end();
