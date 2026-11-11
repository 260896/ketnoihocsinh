<?php
/* QT08 – Tài khoản (chỉ Quản trị viên) */
require __DIR__ . '/../includes/bootstrap.php';
$me = require_staff(true);

$tab = get('tab') === 'hoc-sinh' ? 'hoc-sinh' : 'can-bo';
$err = [];
$v = ['name' => '', 'email' => '', 'role' => 'Xử lý phiếu', 'group' => issue_groups()[0] ?? '', 'reveal' => false];

if (is_post()) {
    $a = post('action');

    if ($a === 'create') {
        $v = ['name' => post('name'), 'email' => post('email'), 'role' => post('role') === 'Quản trị viên' ? 'Quản trị viên' : 'Xử lý phiếu', 'group' => post('group'), 'reveal' => !empty($_POST['reveal'])];
        if ($v['name'] === '') $err['name'] = 'Nhập họ tên.';
        // Kiểm tra lỏng: tên miền chạy thử "abc-trung học.edu.vn" có dấu cách, filter_var() sẽ từ chối
        if (!preg_match('/^[^@\s]+@[^@]+\.[^@]+$/u', $v['email'])) $err['email'] = 'Email không hợp lệ.';
        elseif (user_find_by_email($v['email'])) $err['email'] = 'Email đã có tài khoản.';
        if (!$err) {
            $temp = substr(str_replace(['+', '/', '='], '', base64_encode(random_bytes(9))), 0, 10);
            user_save([
                'id' => new_id('u_'), 'role' => 'staff', 'name' => $v['name'], 'email' => $v['email'],
                'password' => password_hash($temp, PASSWORD_DEFAULT), 'staff_role' => $v['role'],
                'can_reveal' => $v['reveal'], 'group' => $v['group'], 'locked' => false, 'verified' => true, 'created_at' => now_iso(),
            ]);
            audit_log('Tài khoản', null, 'Tạo tài khoản cán bộ ' . $v['name'], $me);
            flash('Đã tạo tài khoản. Mật khẩu tạm (bản chạy thử chưa gửi email): ' . $temp);
            redirect('qt/tai-khoan.php');
        }
    }

    if ($a === 'staff_edit' && ($u = user_find(post('id'))) && $u['role'] === 'staff') {
        $u['staff_role'] = post('role') === 'Quản trị viên' ? 'Quản trị viên' : 'Xử lý phiếu';
        $u['can_reveal'] = !empty($_POST['reveal']);
        $u['locked'] = !empty($_POST['locked']) && $u['id'] !== $me['id']; // không tự khóa mình
        user_save($u);
        audit_log('Tài khoản', null, 'Cập nhật tài khoản cán bộ ' . $u['name'] . ($u['can_reveal'] ? ' (có quyền mở danh tính)' : ''), $me);
        flash('Đã cập nhật ' . $u['name'] . '.');
        redirect('qt/tai-khoan.php');
    }

    if ($a === 'student_lock' && ($u = user_find(post('id'))) && $u['role'] === 'student') {
        $u['locked'] = !$u['locked'];
        user_save($u);
        audit_log('Tài khoản', null, ($u['locked'] ? 'Khóa' : 'Mở khóa') . ' tài khoản ' . $u['name'], $me);
        flash(($u['locked'] ? 'Đã khóa ' : 'Đã mở khóa ') . $u['name'] . '.');
        redirect('qt/tai-khoan.php', ['tab' => 'hoc-sinh']);
    }
}

$staff = staff_all();
$students = array_values(array_filter(users_all(), fn ($u) => $u['role'] === 'student'));
$state = fn ($locked) => $locked ? '<span class="badge badge--danger-solid">Đã khóa</span>' : '<span class="badge badge--info">Hoạt động</span>';

admin_start('Tài khoản', 'Tài khoản', 'Tài khoản cán bộ do quản trị viên tạo. Khóa được tài khoản học sinh vi phạm.', '<a href="#tao" class="btn">Tạo tài khoản cán bộ</a>');
?>
<div class="a-cols">
  <div class="a-col-main">
    <div class="row wrap gap-8">
      <a href="<?= e(url('qt/tai-khoan.php')) ?>" class="chip<?= $tab === 'can-bo' ? ' chip--active' : '' ?>">Cán bộ · <?= count($staff) ?></a>
      <a href="<?= e(url('qt/tai-khoan.php', ['tab' => 'hoc-sinh'])) ?>" class="chip<?= $tab === 'hoc-sinh' ? ' chip--active' : '' ?>">Học sinh · <?= count($students) ?></a>
    </div>

    <?php if ($tab === 'can-bo'): ?>
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th scope="col">Cán bộ</th><th scope="col" style="width:140px">Vai trò</th><th scope="col" style="width:120px">Mở danh tính</th><th scope="col" style="width:110px">Trạng thái</th><th scope="col" style="width:90px"><span class="sr-only">Thao tác</span></th></tr></thead>
          <tbody>
          <?php foreach ($staff as $u): ?>
            <tr>
              <td><?= e($u['name']) ?> · <?= e($u['email']) ?></td>
              <td><?= e($u['staff_role']) ?></td>
              <td><?= $u['can_reveal'] ? '<span class="badge badge--danger">Có</span>' : '<span class="badge badge--muted">Không</span>' ?></td>
              <td><?= $state($u['locked']) ?></td>
              <td>
                <details class="disclosure">
                  <summary class="link">Sửa</summary>
                  <form method="post" class="card" style="position:absolute;right:32px;z-index:3;width:300px;box-shadow:var(--shadow-soft)">
                    <?= csrf_field() ?><input type="hidden" name="action" value="staff_edit"><input type="hidden" name="id" value="<?= e($u['id']) ?>">
                    <p class="t-title"><?= e($u['name']) ?></p>
                    <?= field_select('role', 'Vai trò', ['Xử lý phiếu', 'Quản trị viên'], $u['staff_role'], ['id' => 'role-' . $u['id']]) ?>
                    <label class="row gap-8 t-small"><input type="checkbox" name="reveal" value="1" class="checkbox" <?= $u['can_reveal'] ? 'checked' : '' ?>> Quyền mở danh tính</label>
                    <?php if ($u['id'] !== $me['id']): ?>
                      <label class="row gap-8 t-small"><input type="checkbox" name="locked" value="1" class="checkbox" <?= $u['locked'] ? 'checked' : '' ?>> Khóa tài khoản</label>
                    <?php endif; ?>
                    <button type="submit" class="btn btn--sm">Lưu</button>
                  </form>
                </details>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div>
        <p class="t-h2">Học sinh</p>
        <p class="t-small t-muted">Chỉ hiển thị tên hiển thị và email. Không hiển thị tài khoản nào đã gửi phiếu ẩn danh nào.</p>
      </div>
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th scope="col">Tên hiển thị · Email</th><th scope="col" style="width:130px">Ngày đăng ký</th><th scope="col" style="width:120px">Trạng thái</th><th scope="col" style="width:110px"><span class="sr-only">Thao tác</span></th></tr></thead>
          <tbody>
          <?php foreach ($students as $u): ?>
            <tr>
              <td><?= e($u['name']) ?> · <?= e($u['email']) ?><?= $u['verified'] ? '' : ' <span class="badge badge--muted">Chưa xác thực</span>' ?></td>
              <td><?= e(fmt_dt($u['created_at'], 'd/m/Y')) ?></td>
              <td><?= $state($u['locked']) ?></td>
              <td><?= post_button($u['locked'] ? 'Mở khóa' : 'Khóa', ['action' => 'student_lock', 'id' => $u['id']], $u['locked'] ? 'link' : 'link link--danger') ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$students): ?><tr><td colspan="4" class="table-empty">Chưa có học sinh đăng ký.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <div class="a-col-side" style="flex-basis:380px">
    <form method="post" class="card card--admin" id="tao">
      <?= csrf_field() ?><input type="hidden" name="action" value="create">
      <p class="t-h2">Tạo tài khoản cán bộ</p>
      <?= field_input('name', 'Họ tên *', $v['name'], ['placeholder' => 'Nhập họ tên cán bộ', 'error' => $err['name'] ?? null]) ?>
      <?= field_input('email', 'Email *', $v['email'], ['placeholder' => 'ten@' . SITE['staff_domain'], 'error' => $err['email'] ?? null]) ?>
      <?= field_select('role', 'Vai trò *', ['Xử lý phiếu', 'Quản trị viên'], $v['role']) ?>
      <?= field_select('group', 'Nhóm vấn đề phụ trách', issue_groups(), $v['group']) ?>
      <?= switch_row('reveal', 'Quyền mở danh tính phiếu ẩn danh', 'Chỉ cấp cho người được chỉ định theo quy tắc', $v['reveal']) ?>
      <p class="t-small t-muted">Cán bộ nhận email đặt mật khẩu lần đầu.</p>
      <div class="row gap-10">
        <a href="<?= e(url('qt/tai-khoan.php')) ?>" class="btn btn--outline btn--block">Hủy</a>
        <button type="submit" class="btn btn--block">Tạo tài khoản</button>
      </div>
    </form>
  </div>
</div>
<?php admin_end();
