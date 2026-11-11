<?php
/* SV06 – Mật khẩu mới */
require __DIR__ . '/../includes/bootstrap.php';

$token = get('token');
$user = null;
foreach (users_all() as $u) {
    if ($token !== '' && ($u['reset_token'] ?? '') !== '' && hash_equals($u['reset_token'], $token) && ($u['reset_expires'] ?? 0) > time()) {
        $user = $u;
    }
}
if (!$user) {
    flash('Liên kết đặt lại không hợp lệ hoặc đã hết hạn.', 'error');
    redirect('sv/quen-mat-khau.php');
}

$error = null;
if (is_post()) {
    $pw = $_POST['pw'] ?? '';
    if (mb_strlen($pw) < 8 || !preg_match('/\pL/u', $pw) || !preg_match('/\d/', $pw)) {
        $error = 'Mật khẩu chưa đủ yêu cầu: ít nhất 8 ký tự, có chữ và số.';
    } elseif ($pw !== ($_POST['pw2'] ?? '')) {
        $error = 'Mật khẩu nhập lại chưa khớp.';
    } else {
        $user['password'] = password_hash($pw, PASSWORD_DEFAULT);
        $user['reset_token'] = '';
        user_save($user);
        flash('Đã đổi mật khẩu. Mời bạn đăng nhập.');
        redirect($user['role'] === 'staff' ? 'qt/dang-nhap.php' : 'sv/dang-nhap.php');
    }
}

mobile_start('Mật khẩu mới');
?>
<form method="post" class="stack gap-16">
  <?= csrf_field() ?>
  <span class="icon-circle" style="width:56px;height:56px;background:var(--c-primary-50)"><?= icon('lock', 28, 'var(--c-primary)') ?></span>
  <h2 class="t-h1">Tạo mật khẩu mới</h2>
  <?= field_input('pw', 'Mật khẩu mới', '', ['type' => 'password', 'placeholder' => 'Tối thiểu 8 ký tự', 'autocomplete' => 'new-password', 'required' => true]) ?>
  <?= field_input('pw2', 'Nhập lại mật khẩu mới', '', ['type' => 'password', 'placeholder' => 'Nhập lại mật khẩu', 'autocomplete' => 'new-password', 'required' => true, 'error' => $error]) ?>
  <p class="t-small t-muted">Gồm ít nhất 8 ký tự, có chữ và số.</p>
  <button type="submit" class="btn btn--block">Lưu mật khẩu</button>
</form>
<?php mobile_end();
