<?php
/* QT01 – Đăng nhập cán bộ */
require __DIR__ . '/../includes/bootstrap.php';

if ((current_user()['role'] ?? '') === 'staff') {
    redirect('qt/phieu.php');
}

$email = '';
$error = null;
if (is_post()) {
    $email = post('email');
    [$user, $error] = attempt_login($email, $_POST['pw'] ?? '', 'staff');
    if ($user) {
        login_user($user);
        redirect_after_login('qt/phieu.php');
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
    <?php /* type="text": tên đăng nhập chạy thử có dấu cách và chữ có dấu, ô email của trình duyệt sẽ chặn */ ?>
    <?= field_input('email', 'Tên đăng nhập (email cán bộ)', $email, ['placeholder' => 'ten@' . SITE['staff_domain'], 'autocomplete' => 'username', 'required' => true, 'autocapitalize' => 'off', 'spellcheck' => 'false']) ?>
    <?= field_input('pw', 'Mật khẩu', '', ['type' => 'password', 'placeholder' => 'Nhập mật khẩu', 'autocomplete' => 'current-password', 'required' => true, 'error' => $error]) ?>
    <button type="submit" class="btn btn--block">Đăng nhập</button>
    <div class="panel"><?= note('lock', 'Mọi thao tác phân loại, phân công, phản hồi và mở danh tính đều được ghi nhật ký.') ?></div>
    <div class="demo-box">
      Tài khoản chạy thử (mật khẩu <code><?= e(DEMO_PASSWORD) ?></code>):<br>
      <code>admin@abc-trung học.edu.vn</code> – Quản trị chính<br>
      <code>baotq@abc-trung học.edu.vn</code> – Phụ trách công tác học sinh<br>
      <code>hatl@abc-trung học.edu.vn</code> – Tư vấn, hỗ trợ học sinh<br>
      <code>namph@abc-trung học.edu.vn</code> – Hỗ trợ dự phòng<br>
      <code>tungvt@abc-trung học.edu.vn</code> – Tiếp nhận độc lập
    </div>
    <?= demo_notice() ?>
  </form>
</div>
<?php page_foot();
