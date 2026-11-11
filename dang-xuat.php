<?php
require __DIR__ . '/includes/bootstrap.php';

if (!is_post()) {
    redirect('');
}
$wasStaff = (current_user()['role'] ?? '') === 'staff';
logout_user();
redirect($wasStaff ? 'qt/dang-nhap.php' : 'sv/dang-nhap.php');
