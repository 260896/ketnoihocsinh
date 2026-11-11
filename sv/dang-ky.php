<?php
/* SV01 + SV02 – Đăng ký (kèm trạng thái sai mã tham gia) */
require __DIR__ . '/../includes/bootstrap.php';

$v = ['code' => '', 'email' => '', 'name' => ''];
$err = [];

if (is_post()) {
    $v = ['code' => post('code'), 'email' => post('email'), 'name' => post('name')];
    $pw = $_POST['pw'] ?? '';
    $pw2 = $_POST['pw2'] ?? '';

    if ($v['code'] === '') $err['code'] = 'Nhập mã tham gia do nhà trường cấp.';
    if (!filter_var($v['email'], FILTER_VALIDATE_EMAIL)) {
        $err['email'] = 'Email chưa đúng định dạng.';
    } elseif (user_find_by_email($v['email'])) {
        $err['email'] = 'Email này đã được đăng ký.';
    }
    // Mã + email phải khớp đúng một cặp nhà trường đã nạp (data/codes.json) và chưa được dùng.
    // Chống dò mã: sai nhiều lần trong 15 phút thì tạm khóa (theo email 5 lần, theo IP 30 lần – trường dùng chung IP).
    $tkey = 'join|' . client_ip();
    $ekey = 'join-email|' . mb_strtolower($v['email']);
    if (!$err && ($w = max(throttle_wait($ekey), throttle_wait($tkey, THROTTLE_MAX_IP)))) {
        $err['code'] = throttle_message($w);
    }
    if (!$err) {
        $check = join_code_check($v['code'], $v['email']);
        if ($check === 'not_found') {
            $err['code'] = 'Mã tham gia và email không khớp với danh sách của nhà trường. Kiểm tra lại hoặc liên hệ nhà trường.';
            throttle_fail($tkey);
            throttle_fail($ekey);
        } elseif ($check === 'used') {
            $err['code'] = 'Mã này đã được dùng để đăng ký. Liên hệ nhà trường nếu em chưa tạo tài khoản.';
        }
    }
    if ($v['name'] === '') $err['name'] = 'Nhập tên hiển thị (có thể là biệt danh).';
    if (mb_strlen($pw) < 8 || !preg_match('/\pL/u', $pw) || !preg_match('/\d/', $pw)) $err['pw'] = 'Mật khẩu cần ít nhất 8 ký tự, có chữ và số.';
    if ($pw !== $pw2) $err['pw2'] = 'Mật khẩu nhập lại chưa khớp.';
    if (empty($_POST['agree'])) $err['agree'] = 'Bạn cần đồng ý với Quy tắc sử dụng và Chính sách bảo mật.';

    // Đánh dấu mã đã dùng trước khi tạo tài khoản (chặn 2 người dùng cùng một mã cùng lúc).
    if (!$err && !join_code_use($v['code'])) {
        $err['code'] = 'Mã này vừa được dùng để đăng ký. Liên hệ nhà trường để được cấp mã khác.';
    }

    if (!$err) {
        $newUser = [
            'id' => new_id('u_'), 'role' => 'student', 'name' => $v['name'], 'email' => $v['email'],
            'password' => password_hash($pw, PASSWORD_DEFAULT), 'locked' => false, 'verified' => false,
            'verify_token' => bin2hex(random_bytes(16)), 'verify_expires' => time() + 86400,
            'progress' => [], 'created_at' => now_iso(),
        ];
        user_save($newUser);
        send_verify_mail($newUser);
        $_SESSION['verify_sent_at'] = time();
        $_SESSION['pending_email'] = $v['email'];
        redirect('sv/xac-thuc.php');
    }
}

mobile_start('Đăng ký', ['back' => url('sv/dang-nhap.php')]);
?>
<form method="post" class="stack gap-16" novalidate>
  <?= csrf_field() ?>
  <?= brand() ?>
  <h2 class="t-h1">Tạo tài khoản</h2>
  <p class="t-muted">Nhập mã tham gia và đúng email mà nhà trường đã đăng ký cho em. Mỗi mã chỉ dùng được một lần.</p>
  <?= field_input('code', 'Mã tham gia *', $v['code'], ['placeholder' => 'Mã do nhà trường cung cấp', 'error' => $err['code'] ?? null, 'required' => true]) ?>
  <?= field_input('email', 'Email *', $v['email'], ['type' => 'email', 'placeholder' => 'Email nhà trường đã đăng ký cho em', 'autocomplete' => 'email', 'error' => $err['email'] ?? null, 'required' => true]) ?>
  <?= field_input('name', 'Tên hiển thị *', $v['name'], ['placeholder' => 'Có thể là biệt danh', 'error' => $err['name'] ?? null, 'required' => true]) ?>
  <?= field_input('pw', 'Mật khẩu *', '', ['type' => 'password', 'placeholder' => 'Ít nhất 8 ký tự, có chữ và số', 'autocomplete' => 'new-password', 'error' => $err['pw'] ?? null, 'required' => true, 'minlength' => 8]) ?>
  <?= field_input('pw2', 'Nhập lại mật khẩu *', '', ['type' => 'password', 'placeholder' => 'Nhập lại mật khẩu', 'autocomplete' => 'new-password', 'error' => $err['pw2'] ?? null, 'required' => true]) ?>
  <div class="panel"><?= note('eyeOff', 'Không cần họ tên thật, mã học sinh hay lớp.') ?></div>
  <label class="note" style="cursor:pointer">
    <input type="checkbox" name="agree" value="1" class="checkbox" <?= is_post() && empty($_POST['agree']) ? '' : 'checked' ?>>
    <span>Tôi đồng ý với <a class="link" href="<?= e(url('trang.php', ['p' => 'quy-tac'])) ?>" target="_blank">Quy tắc sử dụng</a> và <a class="link" href="<?= e(url('trang.php', ['p' => 'chinh-sach'])) ?>" target="_blank">Chính sách bảo mật</a> của <?= e(SITE['org_name']) ?>.</span>
  </label>
  <?= field_error($err['agree'] ?? null, 'agree') ?>
  <button type="submit" class="btn btn--block">Đăng ký</button>
  <a href="<?= e(url('sv/dang-nhap.php')) ?>" class="link link--lg t-center d-block">Đã có tài khoản? Đăng nhập</a>
</form>
<?php mobile_end();
