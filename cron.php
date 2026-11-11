<?php
/**
 * Chạy định kỳ nhắc hạn & chuyển người dự phòng – không phụ thuộc lượt truy cập.
 *   Windows (Task Scheduler, mỗi phút):  C:\xampp\php\php.exe C:\xampp\htdocs\project\tieng-anh\cron.php
 *   Linux (crontab):                     * * * * * php /đường/dẫn/cron.php
 * Chỉ chạy từ dòng lệnh.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Forbidden');
}
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
$_SERVER['SCRIPT_NAME'] = '/cron.php';
require __DIR__ . '/includes/bootstrap.php';

$n = sla_run(true);
echo date('c') . " – đã xử lý $n phiếu quá hạn\n";
