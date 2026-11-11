<?php
/* SV04 – Đăng nhập học sinh */
require __DIR__ . '/../includes/bootstrap.php';

if ((current_user()['role'] ?? '') === 'student') {
    redirect('sv/hoc.php');
}

$email = '';
$error = null;
if (is_post()) {
    $email = post('email');
    [$user, $error] = attempt_login($email, $_POST['pw'] ?? '', 'student');
    if ($user) {
        login_user($user);
        redirect_after_login('sv/hoc.php');
    }
    if (($u = user_find_by_email($email)) && !$u['verified']) {
        $_SESSION['pending_email'] = $u['email'];
        $error .= ' Mở trang xác thực để nhận liên kết mới.';
    }
}

auth_start('Đăng nhập', ['subtitle' => 'Học qua tình huống và gửi phản ánh an toàn.']);
?>
  <?php /* Khối này chứa form Đăng xuất riêng → đặt ngoài form đăng nhập (HTML không cho lồng form) */ ?>
  <?= session_switch_notice('student') ?>
<form method="post" class="auth-form">
  <?= csrf_field() ?>
  <?= field_input('email', 'Email', $email, ['type' => 'email', 'placeholder' => 'ten@email.com', 'autocomplete' => 'email', 'required' => true]) ?>
  <?= field_input('pw', 'Mật khẩu', '', ['type' => 'password', 'placeholder' => 'Nhập mật khẩu', 'autocomplete' => 'current-password', 'required' => true, 'error' => $error]) ?>
  <div style="text-align:right"><a href="<?= e(url('sv/quen-mat-khau.php')) ?>" class="link link--lg">Quên mật khẩu?</a></div>
  <button type="submit" class="btn btn--block">Đăng nhập</button>
  <a href="<?= e(url('sv/dang-ky.php')) ?>" class="link link--lg t-center d-block">Chưa có tài khoản? Đăng ký bằng mã tham gia</a>
</form>
  <?php if (SITE['is_demo']): ?>
    <div class="demo-box">Tài khoản chạy thử: <code>E2400249.test@example.com</code> (mật khẩu trong tài liệu nội bộ). Đăng ký mới: mã <code>DEMO-0001</code> + email <code>demo1@example.com</code>.</div>
  <?php endif; ?>
  <?= demo_notice() ?>
  <?= emergency_card() ?>
<?php auth_end();
