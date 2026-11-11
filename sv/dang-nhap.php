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

mobile_start('Đăng nhập', ['no_header' => true]);
?>
<form method="post" class="stack" style="gap:18px;padding-top:28px">
  <?= csrf_field() ?>
  <?= brand() ?>
  <h2 style="font-size:28px;font-weight:700;line-height:1.3">Đăng nhập</h2>
  <p class="t-muted">Học qua tình huống và gửi phản ánh an toàn.</p>
  <?= session_switch_notice('student') ?>
  <?= field_input('email', 'Email', $email, ['type' => 'email', 'placeholder' => 'ten@email.com', 'autocomplete' => 'email', 'required' => true]) ?>
  <?= field_input('pw', 'Mật khẩu', '', ['type' => 'password', 'placeholder' => 'Nhập mật khẩu', 'autocomplete' => 'current-password', 'required' => true, 'error' => $error]) ?>
  <div style="text-align:right"><a href="<?= e(url('sv/quen-mat-khau.php')) ?>" class="link link--lg">Quên mật khẩu?</a></div>
  <button type="submit" class="btn btn--block">Đăng nhập</button>
  <a href="<?= e(url('sv/dang-ky.php')) ?>" class="link link--lg t-center d-block">Chưa có tài khoản? Đăng ký bằng mã tham gia</a>
  <?php if (SITE['is_demo']): ?>
    <div class="demo-box">Tài khoản chạy thử: <code>E2400249.test@example.com</code> / <code><?= e(DEMO_PASSWORD) ?></code>. Đăng ký mới: mã <code>DEMO-0001</code> + email <code>demo1@example.com</code>.</div>
  <?php endif; ?>
  <?= demo_notice() ?>
  <?= emergency_card() ?>
</form>
<?php mobile_end();
