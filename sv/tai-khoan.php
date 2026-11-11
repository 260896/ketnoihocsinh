<?php
/* SV11 – Tài khoản học sinh */
require __DIR__ . '/../includes/bootstrap.php';
$me = require_student();

$open = '';
$err = [];
if (is_post()) {
    $action = post('action');
    if ($action === 'name') {
        $open = 'name';
        if (post('name') === '') {
            $err['name'] = 'Tên hiển thị không được để trống.';
        } else {
            $me['name'] = post('name');
            user_save($me);
            flash('Đã đổi tên hiển thị.');
            redirect('sv/tai-khoan.php');
        }
    }
    if ($action === 'password') {
        $open = 'password';
        $new = $_POST['new'] ?? '';
        if (!password_verify($_POST['old'] ?? '', $me['password'])) {
            $err['old'] = 'Mật khẩu hiện tại không đúng.';
        } elseif (mb_strlen($new) < 8 || !preg_match('/\pL/u', $new) || !preg_match('/\d/', $new)) {
            $err['new'] = 'Mật khẩu mới cần ít nhất 8 ký tự, có chữ và số.';
        } else {
            $me['password'] = password_hash($new, PASSWORD_DEFAULT);
            user_save($me);
            flash('Đã đổi mật khẩu.');
            redirect('sv/tai-khoan.php');
        }
    }
}

$rowStyle = 'padding:14px 0;border-bottom:1px solid var(--c-border)';
$badges = learning_badges($me);
mobile_start('Tài khoản');
?>
<div class="card">
  <div class="row" style="gap:14px">
    <span class="icon-circle" style="width:52px;height:52px;background:var(--c-primary)"><?= icon('user', 26, '#fff') ?></span>
    <span><span class="t-h2 d-block"><?= e($me['name']) ?></span><span class="t-small t-muted"><?= e($me['email']) ?></span></span>
  </div>
</div>

<div class="card" style="gap:10px">
  <p class="t-h2">Huy hiệu học tập</p>
  <?php foreach ($badges as $b): ?>
    <div class="row gap-10"><span class="icon-circle" style="width:36px;height:36px;background:var(--c-primary-50)"><?= icon($b['icon'], 18, 'var(--c-primary)') ?></span><span class="t-small t-strong"><?= e($b['name']) ?></span></div>
  <?php endforeach; ?>
  <?php if (!$badges): ?><p class="t-small t-muted">Hoàn thành một chủ đề để nhận huy hiệu đầu tiên.</p><?php endif; ?>
  <p class="t-small t-muted">Huy hiệu chỉ dành cho việc học, không liên quan đến việc gửi phản ánh.</p>
</div>

<div class="card" style="padding:4px 16px;gap:0">
  <details class="disclosure" <?= $open === 'name' ? 'open' : '' ?> style="<?= $rowStyle ?>">
    <summary class="row gap-12"><?= icon('edit', 20, 'var(--c-primary)') ?><span class="t-title grow">Đổi tên hiển thị</span><span class="when-closed"><?= icon('chevronRight', 18, 'var(--c-muted)') ?></span><span class="when-open"><?= icon('chevronDown', 18, 'var(--c-muted)') ?></span></summary>
    <form method="post" class="stack gap-10" style="margin-top:12px">
      <?= csrf_field() ?><input type="hidden" name="action" value="name">
      <?= field_input('name', 'Tên hiển thị mới', $me['name'], ['error' => $err['name'] ?? null]) ?>
      <button type="submit" class="btn btn--sm">Lưu</button>
    </form>
  </details>
  <details class="disclosure" <?= $open === 'password' ? 'open' : '' ?> style="<?= $rowStyle ?>">
    <summary class="row gap-12"><?= icon('lock', 20, 'var(--c-primary)') ?><span class="t-title grow">Đổi mật khẩu</span><span class="when-closed"><?= icon('chevronRight', 18, 'var(--c-muted)') ?></span><span class="when-open"><?= icon('chevronDown', 18, 'var(--c-muted)') ?></span></summary>
    <form method="post" class="stack gap-10" style="margin-top:12px">
      <?= csrf_field() ?><input type="hidden" name="action" value="password">
      <?= field_input('old', 'Mật khẩu hiện tại', '', ['type' => 'password', 'autocomplete' => 'current-password', 'error' => $err['old'] ?? null]) ?>
      <?= field_input('new', 'Mật khẩu mới', '', ['type' => 'password', 'autocomplete' => 'new-password', 'error' => $err['new'] ?? null]) ?>
      <button type="submit" class="btn btn--sm">Lưu mật khẩu</button>
    </form>
  </details>
  <div class="row gap-12" style="<?= $rowStyle ?>"><?= icon('mail', 20, 'var(--c-primary)') ?><span class="grow"><span class="t-title d-block">Email thông báo</span><span class="t-small t-muted">Chỉ báo “phiếu có cập nhật”</span></span></div>
  <a href="<?= e(url('trang.php', ['p' => 'quy-tac'])) ?>" class="row gap-12" style="<?= $rowStyle ?>;text-decoration:none;color:var(--c-text)"><?= icon('file', 20, 'var(--c-primary)') ?><span class="t-title grow">Quy tắc sử dụng</span><?= icon('chevronRight', 18, 'var(--c-muted)') ?></a>
  <a href="<?= e(url('trang.php', ['p' => 'chinh-sach'])) ?>" class="row gap-12" style="<?= $rowStyle ?>;text-decoration:none;color:var(--c-text)"><?= icon('lock', 20, 'var(--c-primary)') ?><span class="t-title grow">Chính sách bảo mật</span><?= icon('chevronRight', 18, 'var(--c-muted)') ?></a>
  <a href="<?= e(url('trang.php', ['p' => 'an-danh'])) ?>" class="row gap-12" style="padding:14px 0;text-decoration:none;color:var(--c-text)"><?= icon('eyeOff', 20, 'var(--c-primary)') ?><span class="t-title grow">Cơ chế ẩn danh</span><?= icon('chevronRight', 18, 'var(--c-muted)') ?></a>
</div>

<form method="post" action="<?= e(url('dang-xuat.php')) ?>">
  <?= csrf_field() ?>
  <button type="submit" class="btn btn--danger-outline btn--block">Đăng xuất</button>
</form>
<?php mobile_end('Tài khoản');
