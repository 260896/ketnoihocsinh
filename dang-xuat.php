<?php
require __DIR__ . '/includes/bootstrap.php';

if (!is_post()) {
    redirect('');
}
$wasStaff = (current_user()['role'] ?? '') === 'staff';
logout_user();
// ?to=student|staff: đăng xuất để chuyển sang trang đăng nhập loại tài khoản kia
$to = post('to');
redirect($to === 'student' ? 'sv/dang-nhap.php' : ($to === 'staff' ? 'qt/dang-nhap.php' : ($wasStaff ? 'qt/dang-nhap.php' : 'sv/dang-nhap.php')));
