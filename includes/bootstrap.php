<?php
declare(strict_types=1);

/**
 * Nạp ở đầu MỌI trang:  require __DIR__ . '/../includes/bootstrap.php';
 * Khởi tạo session, cấu hình, hàm tiện ích, kho dữ liệu, icon và thành phần giao diện.
 */

define('ROOT', dirname(__DIR__));
define('DATA_DIR', ROOT . '/data');
define('UPLOAD_DIR', DATA_DIR . '/uploads');

date_default_timezone_set('Asia/Ho_Chi_Minh');
mb_internal_encoding('UTF-8');

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

/*
 * BASE_URL: đường dẫn gốc của site, để chạy được cả với `php -S` lẫn
 * trong thư mục con của Apache (vd. http://localhost/project/).
 */
(function () {
    $script = str_replace('\\', '/', (string) realpath($_SERVER['SCRIPT_FILENAME'] ?? ''));
    $root   = str_replace('\\', '/', ROOT);
    $rel    = substr($script, strlen($root));                 // vd. /sv/hoc.php
    $name   = $_SERVER['SCRIPT_NAME'] ?? '';                  // vd. /project/sv/hoc.php
    $base   = ($rel !== '' && str_ends_with($name, $rel)) ? substr($name, 0, -strlen($rel)) : '';
    define('BASE_URL', rtrim($base, '/'));
})();

require ROOT . '/includes/config.php';
require ROOT . '/includes/helpers.php';
require ROOT . '/includes/store.php';
require ROOT . '/includes/seed.php';
require ROOT . '/includes/auth.php';
require ROOT . '/includes/icons.php';
require ROOT . '/includes/ui.php';
require ROOT . '/includes/layout.php';

// Không cho công cụ tìm kiếm lập chỉ mục bản demo – áp dụng cho mọi phản hồi (cả tệp tải về, CSV).
if (SITE['noindex'] && !headers_sent()) {
    header('X-Robots-Tag: noindex, nofollow');
}

// Nhắc hạn / chuyển dự phòng: chạy dự phòng tại đây (tối đa 1 lần/phút).
// Bản chính thức nên chạy thêm cron thật: php cron.php mỗi phút (xem HUONG-DAN.md).
try {
    sla_run();
} catch (Throwable $e) {
    error_log('sla_run: ' . $e->getMessage());
}

// Mọi form POST đều phải có mã CSRF hợp lệ.
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    verify_csrf();
}
