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
    <?= session_switch_notice('staff') ?>
    <p class="t-small t-muted">Dành cho cán bộ quản trị và cán bộ xử lý phiếu. Tài khoản do quản trị viên cấp.</p>
    <?= field_input('email', 'Tên đăng nhập (email cán bộ)', $email, ['placeholder' => 'ten@' . SITE['staff_domain'], 'autocomplete' => 'username', 'required' => true, 'autocapitalize' => 'off', 'spellcheck' => 'false']) ?>
    <?= field_input('pw', 'Mật khẩu', '', ['type' => 'password', 'placeholder' => 'Nhập mật khẩu', 'autocomplete' => 'current-password', 'required' => true, 'error' => $error]) ?>
    <button type="submit" class="btn btn--block">Đăng nhập</button>
    <div class="panel"><?= note('lock', 'Mọi thao tác phân loại, phân công, phản hồi và mở danh tính đều được ghi nhật ký.') ?></div>
    <?php if (SITE['is_demo']): /* Chỉ bản chạy thử – tắt is_demo trong includes/config.php khi dùng thật */ ?>
    <div class="demo-box">
      Tài khoản chạy thử (mật khẩu chỉ có trong tài liệu nội bộ, không hiển thị công khai):<br>
      <code>admin@example.com</code> – NV01 Nguyễn Minh Anh, quản trị, mở được danh tính<br>
      <code>baotq@example.com</code> – NV02 Trần Quốc Bảo, công tác học sinh<br>
      <code>hatl@example.com</code> – NV03 Lê Thu Hà, tư vấn, hỗ trợ học sinh<br>
      <code>namph@example.com</code> – NV04 Phạm Hoàng Nam, dự phòng<br>
      <code>tungvt@example.com</code> – NV05 Võ Thanh Tùng, tiếp nhận độc lập, mở được danh tính<br>
      <code>giaovien@example.com</code> – Giáo viên quản lý học liệu
    </div>
    <?php endif; ?>
    <?= demo_notice() ?>
  </form>
</div>
<?php page_foot();
