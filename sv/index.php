<?php
/* Truy cập /sv/ → trang Học (đã đăng nhập) hoặc trang đăng nhập học sinh. */
require __DIR__ . '/../includes/bootstrap.php';
require_student();
redirect('sv/hoc.php');
