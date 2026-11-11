<?php
/* Truy cập /qt/ → trang mặc định theo vai trò cán bộ, hoặc trang đăng nhập quản trị. */
require __DIR__ . '/../includes/bootstrap.php';
redirect(staff_home(require_staff()));
