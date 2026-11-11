<?php
/**
 * Router cho máy chủ PHP tích hợp (khi chạy bằng `php -S`):
 *   php -S localhost:8000 router.php
 * Chặn truy cập trực tiếp thư mục data/ và includes/ (Apache dùng .htaccess).
 */
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (preg_match('#^/(data|includes|tools|tests|_old-react|Ho_so_[^/]*|Kh[^/]*nghi[^/]*|nguon)(/|$)#', $path) || preg_match('#\.(zip|xlsx|docx|md|sh)$#', $path)) {
    http_response_code(403);
    exit('Forbidden');
}
return false; // để máy chủ phục vụ tệp như bình thường
