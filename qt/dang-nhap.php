<?php
/* QT01 – Đăng nhập cán bộ */
require __DIR__ . '/../includes/bootstrap.php';

if (is_staff($cu = current_user())) {
    redirect(staff_home($cu));
}

$email = '';
$error = null;
if (is_post()) {
    $email = post('email');
    [$user, $error] = attempt_login($email, $_POST['pw'] ?? '', 'staff');
    if ($user) {
        login_user($user);
        redirect_after_login(staff_home($user));
    }
}

page_head('Đăng nhập trang quản trị');
?>
<div class="center-page">
  <form method="post" class="auth-card">
    <?= csrf_field() ?>
    <?= brand() ?>
    <h2 class="t-h1">Đăng nhập trang quản trị</h2>
    <p class="t-small t-muted">Dành cho cán bộ quản trị và cán bộ xử lý phiếu. Tài khoản do quản trị viên cấp.</p>
    <?= field_input('email', 'Tên đăng nhập (email cán bộ)', $email, ['placeholder' => 'ten@' . SITE['staff_domain'], 'autocomplete' => 'username', 'required' => true, 'autocapitalize' => 'off', 'spellcheck' => 'false']) ?>
    <?= field_input('pw', 'Mật khẩu', '', ['type' => 'password', 'placeholder' => 'Nhập mật khẩu', 'autocomplete' => 'current-password', 'required' => true, 'error' => $error]) ?>
    <button type="submit" class="btn btn--block">Đăng nhập</button>
    <div class="panel"><?= note('lock', 'Mọi thao tác phân loại, phân công, phản hồi và mở danh tính đều được ghi nhật ký.') ?></div>
    <?php if (SITE['is_demo']): /* Chỉ bản chạy thử – tắt is_demo trong includes/config.php khi dùng thật */ ?>
    <div class="demo-box">
      Tài khoản chạy thử (mật khẩu <code><?= e(DEMO_PASSWORD) ?></code>):<br>
      <code>admin@abc-trunghoc.edu.vn</code> – Nguyễn Minh Anh, quản trị chính, mở được danh tính<br>
      <code>baotq@abc-trunghoc.edu.vn</code> – Phụ trách công tác học sinh<br>
      <code>hatl@abc-trunghoc.edu.vn</code> – Tư vấn, hỗ trợ học sinh<br>
      <code>namph@abc-trunghoc.edu.vn</code> – Hỗ trợ dự phòng<br>
      <code>tungvt@abc-trunghoc.edu.vn</code> – Tiếp nhận độc lập, mở được danh tính<br>
      <code>giaovien@abc-trunghoc.edu.vn</code> – Giáo viên quản lý học liệu
    </div>
    <?php endif; ?>
    <?= demo_notice() ?>
  </form>
</div>
<?php page_foot();
