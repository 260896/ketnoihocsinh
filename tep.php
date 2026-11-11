<?php
/**
 * Trả tệp đã tải lên (lưu trong data/uploads – không truy cập trực tiếp được).
 *   ?k=img&f=<đường dẫn>          Hình minh họa tình huống (người đã đăng nhập)
 *   ?k=doc&id=<id tài liệu>        Tài liệu học (đã công bố, hoặc cán bộ)
 *   ?k=att&t=<mã phiếu>&i=<vị trí> Tệp đính kèm phiếu (chủ phiếu hoặc cán bộ; cán bộ xem bản gốc được ghi nhật ký)
 */
require __DIR__ . '/includes/bootstrap.php';

$me = current_user();
if (!$me) {
    http_response_code(403);
    exit('Bạn cần đăng nhập.');
}

$path = null;
$downloadName = null;
$mustBeIn = UPLOAD_DIR; // thư mục con bắt buộc của tệp được trả

switch (get('k')) {
    case 'img':
        // Chỉ ảnh minh họa trong data/uploads/images – chặn "images/../tickets/…"
        $path = get('f');
        $mustBeIn = UPLOAD_DIR . '/images';
        if (!str_starts_with($path, 'images/') || str_contains($path, '..') || str_contains($path, '\\')) $path = null;
        break;

    case 'doc':
        foreach (docs_all() as $d) {
            if ($d['id'] === get('id') && $d['path'] && ($d['status'] === 'Đã công bố' || can_manage_learning($me))) {
                $path = $d['path'];
                $downloadName = $d['name'];
            }
        }
        break;

    case 'att':
        // Mặc định trả BẢN ĐÃ XÓA SIÊU DỮ LIỆU (nếu có).
        // Bản gốc (?goc=1): chỉ cán bộ có thẩm quyền, và luôn ghi nhật ký.
        $t = ticket_find(get('t'));
        $att = $t['attachments'][(int) get('i')] ?? null;
        $mustBeIn = UPLOAD_DIR . '/tickets';
        if ($t && $att && (can_access_ticket($me, $t) || ($me['role'] === 'student' && ticket_is_owned_by($t, $me)))) {
            $downloadName = $att['name'];
            if (get('goc') === '1') {
                if (!can_view_original($me)) {
                    http_response_code(403);
                    exit('Chỉ cán bộ có thẩm quyền mới xem được bản gốc.');
                }
                $path = $att['path'];
                audit_log('Tải tệp gốc', $t['code'], $att['name'] . ' (bản gốc kèm siêu dữ liệu)', $me);
            } elseif (!empty($att['clean_path'])) {
                $path = $att['clean_path'];
            } elseif ($me['role'] === 'staff') {
                // Loại tệp chưa xóa được siêu dữ liệu (PDF, MP4…): cán bộ phải mở bằng nút "bản gốc"
                http_response_code(403);
                exit('Tệp này chưa có bản đã xóa siêu dữ liệu. Dùng nút “Xem bản gốc (ghi nhật ký)”.');
            } else {
                $path = $att['path']; // chủ phiếu tải lại chính tệp của mình
            }
        }
        break;
}

$full = $path ? realpath(UPLOAD_DIR . '/' . $path) : false;
$base = realpath($mustBeIn);
if (!$full || !$base || !str_starts_with($full, $base . DIRECTORY_SEPARATOR) || !is_file($full)) {
    http_response_code(404);
    exit('Không tìm thấy tệp.');
}

$mime = (new finfo(FILEINFO_MIME_TYPE))->file($full) ?: 'application/octet-stream';
header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($full));
header('X-Content-Type-Options: nosniff');
$inline = str_starts_with($mime, 'image/') || str_starts_with($mime, 'video/') || $mime === 'application/pdf';
header('Accept-Ranges: none');
header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . '; filename*=UTF-8\'\'' . rawurlencode($downloadName ?? basename($full)));
session_write_close(); // không giữ khóa session khi gửi tệp lớn (video)
readfile($full);
