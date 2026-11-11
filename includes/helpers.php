<?php
declare(strict_types=1);

/** Escape HTML. Dùng cho MỌI dữ liệu in ra trang. */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Tạo URL tuyệt đối trong site: url('sv/hoc.php', ['a' => 1]). */
function url(string $path = '', array $query = []): string
{
    $u = BASE_URL . '/' . ltrim($path, '/');
    $query = array_filter($query, fn ($v) => $v !== null && $v !== '');
    return $query ? $u . '?' . http_build_query($query) : $u;
}

function redirect(string $path, array $query = []): never
{
    header('Location: ' . url($path, $query));
    exit;
}

/** Quay về chính trang hiện tại (sau khi xử lý POST). */
function redirect_back(): never
{
    header('Location: ' . ($_SERVER['REQUEST_URI'] ?? url()));
    exit;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

/** Lấy giá trị POST dạng chuỗi đã trim. */
function post(string $key, string $default = ''): string
{
    $v = $_POST[$key] ?? $default;
    return is_string($v) ? trim($v) : $default;
}

function get(string $key, string $default = ''): string
{
    $v = $_GET[$key] ?? $default;
    return is_string($v) ? trim($v) : $default;
}

// ---------------- Thông báo nhanh (flash) ----------------

function flash(string $message, string $type = 'ok'): void
{
    $_SESSION['flash'][] = ['msg' => $message, 'type' => $type];
}

function take_flashes(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

// ---------------- CSRF ----------------

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $sent = $_POST['_csrf'] ?? '';
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(419);
        exit('Phiên làm việc đã hết hạn. Vui lòng quay lại và thử lại.');
    }
}

// ---------------- Thời gian ----------------

function now_iso(): string
{
    return date('c');
}

function fmt_dt(?string $iso, string $format = 'd/m/Y H:i'): string
{
    if (!$iso) {
        return '—';
    }
    $t = strtotime($iso);
    return $t ? date($format, $t) : '—';
}

function time_ago(string $iso): string
{
    $diff = time() - (int) strtotime($iso);
    if ($diff < 60) return 'vừa xong';
    if ($diff < 3600) return floor($diff / 60) . ' phút trước';
    if ($diff < 86400) return floor($diff / 3600) . ' giờ trước';
    return floor($diff / 86400) . ' ngày trước';
}

/** Đổi số phút ra "25 phút" / "3 giờ" / "2 ngày". */
function human_minutes(int $min): string
{
    if ($min < 60) return $min . ' phút';
    if ($min < 1440) return floor($min / 60) . ' giờ';
    return floor($min / 1440) . ' ngày';
}

// ---------------- Giờ trực ----------------

/** `?ngoaigio=1` / `?ngoaigio=0` trên URL để xem trước trạng thái ngoài giờ. */
function is_out_of_hours(): bool
{
    $o = $_GET['ngoaigio'] ?? null;
    if ($o === '1') return true;
    if ($o === '0') return false;

    $c = config();
    $nowMin = (int) date('G') * 60 + (int) date('i');
    $inDay  = in_array((int) date('w'), $c['duty_days'], true);
    return !($inDay && $nowMin >= duty_minutes($c['duty_start']) && $nowMin < duty_minutes($c['duty_end']));
}

function duty_minutes(string $hhmm): int
{
    [$h, $m] = array_map('intval', explode(':', $hhmm) + [0, 0]);
    return $h * 60 + $m;
}

/** Thời điểm sớm nhất bộ phận tiếp nhận làm việc (bây giờ nếu đang trong giờ). */
function next_duty_start(?int $from = null): int
{
    $c = config();
    $from ??= time();
    $start = duty_minutes($c['duty_start']);
    $end = duty_minutes($c['duty_end']);
    for ($d = 0; $d < 8; $d++) {
        $day = strtotime("today +$d days", $from);
        if (!in_array((int) date('w', $day), $c['duty_days'], true)) continue;
        $open = $day + $start * 60;
        $close = $day + $end * 60;
        if ($from < $open) return $open;
        if ($from < $close) return $from;
    }
    return $from;
}

/** Cộng N ngày làm việc (theo ngày trực trong cấu hình), giữ nguyên giờ trong ngày. */
function add_working_days(int $ts, int $days): int
{
    $workDays = config()['duty_days'];
    while ($days > 0) {
        $ts = strtotime('+1 day', $ts);
        if (in_array((int) date('w', $ts), $workDays, true)) $days--;
    }
    return $ts;
}

/** Nhãn thời gian dự kiến bắt đầu xử lý, vd. "08:00 Thứ Hai, 10/11/2026". */
function expected_processing_label(): string
{
    $ts = next_duty_start();
    if (is_out_of_hours() && $ts <= time()) {
        // Đang xem trước bằng ?ngoaigio=1 trong giờ trực: lấy ca làm việc kế tiếp
        $ts = next_duty_start(strtotime('tomorrow'));
    }
    $names = ['Chủ nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];
    return date('H:i', $ts) . ' ' . $names[(int) date('w', $ts)] . ', ' . date('d/m/Y', $ts);
}

function hotline(): string
{
    $h = trim((string) (config()['hotline'] ?? ''));
    return $h !== '' ? $h : SITE['hotline'];
}

function tel_href(): string
{
    return 'tel:' . preg_replace('/[^0-9+]/', '', hotline());
}

function duty_label(): string
{
    $c = config();
    $days = $c['duty_days'];
    $names = [0 => 'CN', 1 => 'T2', 2 => 'T3', 3 => 'T4', 4 => 'T5', 5 => 'T6', 6 => 'T7'];
    $dayText = $days === [1, 2, 3, 4, 5] ? 'Thứ 2 – Thứ 6'
        : ($days === [1, 2, 3, 4, 5, 6] ? 'Thứ 2 – Thứ 7'
        : (count($days) === 7 ? 'Cả tuần' : implode(', ', array_map(fn ($d) => $names[$d], $days))));
    return $c['duty_start'] . '–' . $c['duty_end'] . ', ' . $dayText;
}

function new_id(string $prefix = ''): string
{
    return $prefix . bin2hex(random_bytes(6));
}

function slugify(string $s): string
{
    $s = mb_strtolower($s);
    $map = [
        'a' => 'àáạảãâầấậẩẫăằắặẳẵ', 'e' => 'èéẹẻẽêềếệểễ', 'i' => 'ìíịỉĩ',
        'o' => 'òóọỏõôồốộổỗơờớợởỡ', 'u' => 'ùúụủũưừứựửữ', 'y' => 'ỳýỵỷỹ', 'd' => 'đ',
    ];
    foreach ($map as $to => $from) {
        $s = preg_replace('/[' . $from . ']/u', $to, $s);
    }
    $s = trim((string) preg_replace('/[^a-z0-9]+/', '-', $s), '-');
    return $s !== '' ? $s : new_id('cd-');
}

function format_size(int $bytes): string
{
    if ($bytes < 1048576) return max(1, (int) round($bytes / 1024)) . ' KB';
    return str_replace('.', ',', (string) round($bytes / 1048576, 1)) . ' MB';
}

/** MIME thực (theo finfo) được chấp nhận cho từng đuôi tệp. */
const UPLOAD_MIMES = [
    'pdf'  => ['application/pdf'],
    'jpg'  => ['image/jpeg'],
    'jpeg' => ['image/jpeg'],
    'png'  => ['image/png'],
    'webp' => ['image/webp'],
    'mp4'  => ['video/mp4', 'video/quicktime', 'video/x-m4v', 'application/mp4'],
];

/**
 * Lưu một tệp tải lên vào data/uploads/<thư mục>.
 * Trả về ['name','size','path'] hoặc chuỗi lỗi.
 */
function save_upload(array $file, string $folder, array $allowedExt, int $maxMb): array|string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return in_array($file['error'] ?? 0, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
            ? 'Mỗi tệp tối đa ' . $maxMb . ' MB, em vui lòng chọn tệp nhỏ hơn.'
            : 'Tải tệp không thành công, em thử lại. Nội dung đã nhập vẫn được giữ.';
    }
    $name = basename((string) $file['name']);
    $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    // Câu báo lỗi theo hồ sơ mục 5
    if (!in_array($ext, $allowedExt, true)) {
        return '"' . $name . '": Hệ thống chỉ nhận ' . strtoupper(implode(', ', $allowedExt)) . '.';
    }
    if ((int) $file['size'] === 0) {
        return '"' . $name . '": Tệp rỗng, em vui lòng chọn tệp khác.';
    }
    // 10 MB = 10.000.000 byte (hồ sơ mục 5): 10.000.000 byte được nhận, 10.000.001 byte bị từ chối
    if ($file['size'] > $maxMb * 1000000) {
        return '"' . $name . '": Mỗi tệp tối đa ' . $maxMb . ' MB, em vui lòng chọn tệp nhỏ hơn.';
    }
    // Kiểm tra MIME thực theo nội dung tệp, không tin vào đuôi tệp (CLAUDE.md mục 7)
    $mime = is_file((string) $file['tmp_name']) ? (string) (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']) : '';
    if (!in_array($mime, UPLOAD_MIMES[$ext] ?? [], true)) {
        return 'Nội dung tệp "' . $name . '" không đúng định dạng ' . strtoupper($ext) . '.';
    }
    $dir = UPLOAD_DIR . '/' . $folder;
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    $stored = new_id() . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $stored)) {
        return 'Không lưu được tệp.';
    }
    return ['name' => $name, 'size' => format_size((int) $file['size']), 'path' => $folder . '/' . $stored];
}

/**
 * Tạo bản sao đã xóa siêu dữ liệu (GPS, thiết bị, thời điểm chụp, ghi chú…)
 * cho ảnh JPEG/PNG. Viết bằng PHP thuần – không cần GD/Imagick.
 * Trả về true nếu đã tạo được $dest, false nếu loại tệp không hỗ trợ / tệp lỗi.
 */
function strip_metadata(string $src, string $dest, string $ext): bool
{
    $data = file_get_contents($src);
    if ($data === false) return false;

    $clean = match ($ext) {
        'jpg', 'jpeg' => strip_jpeg($data),
        'png'         => strip_png($data),
        default       => null,
    };
    return $clean !== null && file_put_contents($dest, $clean) !== false;
}

/** JPEG: bỏ APP1 (EXIF/XMP – chứa GPS, máy chụp), APP13 (IPTC), COM (ghi chú). */
function strip_jpeg(string $d): ?string
{
    if (substr($d, 0, 2) !== "\xFF\xD8") return null;
    $out = "\xFF\xD8";
    $pos = 2;
    $len = strlen($d);
    while ($pos + 4 <= $len) {
        if ($d[$pos] !== "\xFF") return null;               // cấu trúc lỗi
        $marker = ord($d[$pos + 1]);
        if ($marker === 0xFF) { $pos++; continue; }          // byte đệm
        if ($marker === 0xDA) {                              // SOS: phần ảnh – giữ nguyên tới hết
            return $out . substr($d, $pos);
        }
        if ($marker === 0xD9) break;                         // EOI
        $segLen = unpack('n', substr($d, $pos + 2, 2))[1];
        if ($segLen < 2 || $pos + 2 + $segLen > $len) return null;
        $drop = $marker === 0xE1 || $marker === 0xED || $marker === 0xFE;
        if (!$drop) $out .= substr($d, $pos, 2 + $segLen);
        $pos += 2 + $segLen;
    }
    return $out . "\xFF\xD9";
}

/** PNG: bỏ các khối chữ/EXIF/thời gian: tEXt, zTXt, iTXt, eXIf, tIME. */
function strip_png(string $d): ?string
{
    $sig = "\x89PNG\r\n\x1a\n";
    if (substr($d, 0, 8) !== $sig) return null;
    $out = $sig;
    $pos = 8;
    $len = strlen($d);
    while ($pos + 12 <= $len) {
        $chunkLen = unpack('N', substr($d, $pos, 4))[1];
        $type = substr($d, $pos + 4, 4);
        $total = 12 + $chunkLen;
        if ($pos + $total > $len) return null;
        if (!in_array($type, ['tEXt', 'zTXt', 'iTXt', 'eXIf', 'tIME'], true)) {
            $out .= substr($d, $pos, $total);
        }
        $pos += $total;
        if ($type === 'IEND') break;
    }
    return $out;
}

/** Lưu tệp đính kèm phiếu: bản gốc + bản đã xóa siêu dữ liệu (nếu là ảnh JPEG/PNG). */
function save_ticket_upload(array $file, array $allowedExt, int $maxMb): array|string
{
    $r = save_upload($file, 'tickets', $allowedExt, $maxMb);
    if (is_string($r)) return $r;
    $ext = strtolower(pathinfo($r['path'], PATHINFO_EXTENSION));
    $cleanPath = preg_replace('/\.(\w+)$/', '.clean.$1', $r['path']);
    $r['clean_path'] = strip_metadata(UPLOAD_DIR . '/' . $r['path'], UPLOAD_DIR . '/' . $cleanPath, $ext) ? $cleanPath : '';
    return $r;
}

/** Gom mảng $_FILES['x'] dạng nhiều tệp thành danh sách từng tệp. */
function files_list(string $field): array
{
    $f = $_FILES[$field] ?? null;
    if (!$f) return [];
    if (!is_array($f['name'])) return [$f];
    $out = [];
    foreach ($f['name'] as $i => $n) {
        if ($n === '') continue;
        $out[] = ['name' => $n, 'type' => $f['type'][$i], 'tmp_name' => $f['tmp_name'][$i], 'error' => $f['error'][$i], 'size' => $f['size'][$i]];
    }
    return $out;
}
