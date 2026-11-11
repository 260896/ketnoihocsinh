<?php
declare(strict_types=1);

/**
 * Kho dữ liệu dạng tệp JSON trong thư mục data/.
 * Mỗi "bảng" là một tệp: topics, tickets, users, audit, docs, config, codes.
 * Khi chuyển sang MySQL, chỉ cần viết lại các hàm trong tệp này.
 */

function data_file(string $name): string
{
    return DATA_DIR . '/' . $name . '.json';
}

/** Bộ nhớ đệm trong một lượt request. */
$GLOBALS['__store'] = [];

function load(string $name): array
{
    if (isset($GLOBALS['__store'][$name])) {
        return $GLOBALS['__store'][$name];
    }
    $file = data_file($name);
    if (!is_file($file)) {
        save($name, seed_data($name));
    }
    $raw = (string) file_get_contents($file);
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        // Tệp hỏng: dừng lại thay vì coi là rỗng (tránh ghi đè mất dữ liệu ở lần lưu sau).
        throw new RuntimeException('Tệp dữ liệu ' . $file . ' bị lỗi định dạng JSON. Khôi phục bản sao lưu hoặc xóa tệp để tạo lại dữ liệu mẫu.');
    }
    return $GLOBALS['__store'][$name] = $data;
}

function save(string $name, array $data): void
{
    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0775, true);
    }
    // INVALID_UTF8_SUBSTITUTE: dữ liệu nhập sai mã hóa không làm hỏng cả tệp.
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    if ($json === false) {
        throw new RuntimeException('Không ghi được dữ liệu ' . $name . ': ' . json_last_error_msg());
    }
    // Ghi ra tệp tạm rồi đổi tên → không bao giờ để lại tệp ghi dở/rỗng.
    $file = data_file($name);
    $tmp = $file . '.' . bin2hex(random_bytes(4)) . '.tmp';
    if (file_put_contents($tmp, $json, LOCK_EX) === false || !rename($tmp, $file)) {
        @unlink($tmp);
        throw new RuntimeException('Không ghi được tệp ' . $file);
    }
    $GLOBALS['__store'][$name] = $data;
}

/* ======================= Cấu hình ======================= */

function config(): array
{
    return load('config') + seed_data('config');
}

function config_save(array $c): void
{
    save('config', $c);
}

function issue_groups(): array
{
    return config()['groups'];
}

/* ======================= Chủ đề & tình huống ======================= */

/** Danh sách chủ đề (đã sắp theo thứ tự). Mỗi chủ đề chứa mảng 'situations'. */
function topics_all(): array
{
    $topics = load('topics');
    usort($topics, fn ($a, $b) => ($a['order'] ?? 0) <=> ($b['order'] ?? 0));
    foreach ($topics as &$t) {
        usort($t['situations'], fn ($a, $b) => ($a['order'] ?? 0) <=> ($b['order'] ?? 0));
    }
    return $topics;
}

function topics_save(array $topics): void
{
    save('topics', array_values($topics));
}

function topic_find(string $id): ?array
{
    foreach (topics_all() as $t) {
        if ($t['id'] === $id) return $t;
    }
    return null;
}

/** Chỉ các tình huống đã công bố (học sinh thấy). */
function published_situations(array $topic): array
{
    return array_values(array_filter($topic['situations'], fn ($s) => $s['status'] === 'Đã công bố'));
}

/** Tìm tình huống theo id → [chủ đề, tình huống] hoặc null. */
function situation_find(string $id): ?array
{
    foreach (topics_all() as $t) {
        foreach ($t['situations'] as $s) {
            if ($s['id'] === $id) return [$t, $s];
        }
    }
    return null;
}

/** Thêm mới hoặc cập nhật một tình huống, có thể chuyển sang chủ đề khác. */
function situation_upsert(string $topicId, array $situation): void
{
    $topics = load('topics');
    foreach ($topics as &$t) {
        $t['situations'] = array_values(array_filter($t['situations'], fn ($s) => $s['id'] !== $situation['id']));
    }
    unset($t);
    foreach ($topics as &$t) {
        if ($t['id'] === $topicId) {
            $t['situations'][] = $situation;
        }
    }
    unset($t);
    topics_save($topics);
}

function situation_update(string $id, callable $fn): void
{
    $topics = load('topics');
    foreach ($topics as &$t) {
        foreach ($t['situations'] as &$s) {
            if ($s['id'] === $id) $s = $fn($s);
        }
    }
    unset($t, $s);
    topics_save($topics);
}

function situation_delete(string $id): void
{
    $topics = load('topics');
    foreach ($topics as &$t) {
        $t['situations'] = array_values(array_filter($t['situations'], fn ($s) => $s['id'] !== $id));
    }
    unset($t);
    topics_save($topics);
}

/* ======================= Tài liệu ======================= */

function docs_all(): array
{
    return load('docs');
}

function docs_save(array $docs): void
{
    save('docs', array_values($docs));
}

/* ======================= Người dùng ======================= */

function users_all(): array
{
    return load('users');
}

function user_find(?string $id): ?array
{
    if (!$id) return null;
    foreach (users_all() as $u) {
        if ($u['id'] === $id) return $u;
    }
    return null;
}

function user_find_by_email(string $email): ?array
{
    $email = mb_strtolower(trim($email));
    foreach (users_all() as $u) {
        if (mb_strtolower($u['email']) === $email) return $u;
    }
    return null;
}

/** Thêm mới (nếu chưa có id) hoặc cập nhật người dùng. */
function user_save(array $user): void
{
    $users = users_all();
    $found = false;
    foreach ($users as &$u) {
        if ($u['id'] === $user['id']) {
            $u = $user;
            $found = true;
        }
    }
    unset($u);
    if (!$found) $users[] = $user;
    save('users', $users);
}

function staff_all(): array
{
    return array_values(array_filter(users_all(), fn ($u) => $u['role'] === 'staff'));
}

function staff_name(?string $id): string
{
    if (!$id) return 'Hàng chờ phân loại';
    return user_find($id)['name'] ?? '—';
}

/* ======================= Mã tham gia ======================= */
/*
 * Tệp data/codes.json: danh sách cặp (mã, email) do nhà trường nạp sẵn.
 * Học sinh chỉ đăng ký được khi nhập đúng cả mã lẫn email của một cặp chưa dùng; mỗi cặp dùng 1 lần.
 */

/** 4–32 ký tự: chữ không dấu, số, gạch ngang, gạch dưới. */
const JOIN_CODE_PATTERN = '/^[A-Z0-9][A-Z0-9_-]{3,31}$/';

/** Bỏ khoảng trắng, viết hoa: " hs-001 " → "HS-001". */
function join_code_normalize(string $code): string
{
    return strtoupper((string) preg_replace('/\s+/u', '', $code));
}

function join_codes_all(): array
{
    return load('codes');
}

function join_code_find(string $code): ?array
{
    $code = join_code_normalize($code);
    foreach (load('codes') as $c) {
        if ($c['code'] === $code) return $c;
    }
    return null;
}

function join_email_normalize(string $email): string
{
    return mb_strtolower(trim($email));
}

/**
 * Nạp thêm các cặp ['code' => …, 'email' => …]. Mã để trống → hệ thống tự sinh.
 * Bỏ qua: mã sai định dạng, email sai, mã đã có, email đã được gán cho mã khác.
 * Trả về [danh sách cặp đã thêm, danh sách "giá trị – lý do" bị bỏ qua].
 */
function join_codes_add(array $entries): array
{
    $all = load('codes');
    $haveCode = array_flip(array_column($all, 'code'));
    $haveEmail = array_flip(array_filter(array_map(fn ($c) => $c['email'] ?? '', $all)));
    $added = [];
    $skipped = [];
    $now = now_iso();
    foreach ($entries as $e) {
        $email = join_email_normalize((string) ($e['email'] ?? ''));
        $code = join_code_normalize((string) ($e['code'] ?? ''));
        if ($code === '') {
            do { $code = join_code_random(); } while (isset($haveCode[$code]));
        }
        $label = $code . ($email !== '' ? ' / ' . $email : '');
        if (!preg_match(JOIN_CODE_PATTERN, $code)) { $skipped[] = $label . ' – mã sai định dạng'; continue; }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $skipped[] = $label . ' – email không hợp lệ'; continue; }
        if (isset($haveCode[$code])) { $skipped[] = $label . ' – mã đã có'; continue; }
        if (isset($haveEmail[$email])) { $skipped[] = $label . ' – email đã được cấp mã'; continue; }
        $row = ['code' => $code, 'email' => $email, 'used' => false, 'created_at' => $now, 'used_at' => null];
        $all[] = $row;
        $added[] = $row;
        $haveCode[$code] = $haveEmail[$email] = true;
    }
    if ($added) save('codes', $all);
    return [$added, $skipped];
}

/**
 * Kiểm tra cặp mã + email khi đăng ký.
 * Trả về 'ok' | 'not_found' (mã không có hoặc email không khớp) | 'used'.
 */
function join_code_check(string $code, string $email): string
{
    $c = join_code_find($code);
    if (!$c || ($c['email'] ?? '') === '' || $c['email'] !== join_email_normalize($email)) return 'not_found';
    return $c['used'] ? 'used' : 'ok';
}

/** Đổi email của một mã chưa dùng. Trả về thông báo lỗi hoặc null nếu thành công. */
function join_code_set_email(string $code, string $email): ?string
{
    $code = join_code_normalize($code);
    $email = join_email_normalize($email);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return 'Email không hợp lệ.';
    $all = load('codes');
    foreach ($all as $c) {
        if (($c['email'] ?? '') === $email && $c['code'] !== $code) return 'Email này đã được cấp mã ' . $c['code'] . '.';
    }
    foreach ($all as &$c) {
        if ($c['code'] === $code) {
            if ($c['used']) return 'Mã đã dùng, không đổi được email.';
            $c['email'] = $email;
            save('codes', $all);
            return null;
        }
    }
    return 'Không tìm thấy mã.';
}

/** Sinh mã ngẫu nhiên dạng XXXX-XXXX, bỏ các ký tự dễ nhầm (0/O, 1/I/L). Chưa lưu vào tệp. */
function join_code_random(): string
{
    $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
    do {
        $s = '';
        for ($i = 0; $i < 8; $i++) $s .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        $code = substr($s, 0, 4) . '-' . substr($s, 4);
    } while (join_code_find($code));
    return $code;
}

/** Đánh dấu mã đã dùng. Trả về false nếu mã không tồn tại hoặc đã dùng rồi. */
function join_code_use(string $code): bool
{
    $code = join_code_normalize($code);
    $all = load('codes');
    foreach ($all as &$c) {
        if ($c['code'] === $code) {
            if ($c['used']) return false;
            $c['used'] = true;
            $c['used_at'] = now_iso();
            save('codes', $all);
            return true;
        }
    }
    return false;
}

/** Xóa một mã chưa dùng (mã đã dùng giữ lại để không bị nạp lại). */
function join_code_delete(string $code): bool
{
    $code = join_code_normalize($code);
    $all = load('codes');
    $left = array_values(array_filter($all, fn ($c) => $c['code'] !== $code || $c['used']));
    if (count($left) === count($all)) return false;
    save('codes', $left);
    return true;
}

/* ======================= Phiếu ======================= */

function tickets_all(): array
{
    $t = load('tickets');
    usort($t, fn ($a, $b) => strcmp($b['created_at'], $a['created_at']));
    return $t;
}

function ticket_find(string $code): ?array
{
    foreach (load('tickets') as $t) {
        if ($t['code'] === $code) return $t;
    }
    return null;
}

function ticket_save(array $ticket): void
{
    $all = load('tickets');
    $found = false;
    foreach ($all as &$t) {
        if ($t['code'] === $ticket['code']) {
            $t = $ticket;
            $found = true;
        }
    }
    unset($t);
    if (!$found) $all[] = $ticket;
    save('tickets', $all);
}

/** Mã phiếu dạng KN-<năm>-<số thứ tự 4 chữ số>, đánh số lại từ 0001 mỗi năm. */
function next_ticket_code(): string
{
    $year = date('Y');
    $max = 0;
    foreach (load('tickets') as $t) {
        if (preg_match('/^KN-' . $year . '-(\d+)$/', $t['code'], $m)) {
            $max = max($max, (int) $m[1]);
        }
    }
    return 'KN-' . $year . '-' . str_pad((string) ($max + 1), 4, '0', STR_PAD_LEFT);
}

/**
 * Tạo phiếu từ bản nháp của học sinh. Tự chuyển tới đầu mối theo bảng cấu hình;
 * nhóm "Chưa rõ" vào hàng chờ phân loại. Phiếu khẩn có hạn xác nhận ngắn.
 */
function ticket_create(array $owner, array $draft): array
{
    $cfg = config();
    $urgent = $draft['urgent'] === '1';
    $anonymous = $draft['anonymous'] !== '0';
    $route = $cfg['routing'][$draft['group']] ?? ['primary' => ''];
    $assignee = $route['primary'] ?: null;
    // Khẩn: xác nhận trong N phút. Thường: phản hồi lần đầu trong N ngày làm việc,
    // tính từ lúc bộ phận bắt đầu làm việc (nếu gửi ngoài giờ).
    $deadline = $urgent
        ? date('c', time() + (int) $cfg['urgent_ack_minutes'] * 60)
        : date('c', add_working_days(next_duty_start(), (int) ($cfg['first_reply_days'] ?? 2)));
    $now = now_iso();

    $ticket = [
        'code'        => next_ticket_code(),
        'owner_id'    => $owner['id'],
        'type'        => $draft['type'],
        'group'       => $draft['group'],
        'urgent'      => $urgent,
        'anonymous'   => $anonymous,
        'status'      => 'Đã tiếp nhận',
        'priority'    => $urgent ? 'Khẩn' : 'Thường',
        'created_at'  => $now,
        'deadline'    => $deadline,
        'assignee'    => $assignee,
        'description' => $draft['description'],
        'occurred_at' => $draft['occurred_at'] ? date('c', (int) strtotime($draft['occurred_at'])) : '',
        'location'    => $draft['location'],
        'attachments' => $draft['files'],
        'messages'    => [[
            'from' => 'system', 'author_id' => null, 'time' => $now,
            'text' => $urgent
                ? 'Hệ thống đã tiếp nhận phản ánh khẩn của em và báo cho cán bộ trực. Đây chưa phải phản hồi của cán bộ.'
                : 'Hệ thống đã tiếp nhận phản ánh. Đây chưa phải phản hồi của cán bộ.'
                  . (is_out_of_hours() ? ' Phản ánh được gửi ngoài giờ làm việc, dự kiến được xử lý từ ' . expected_processing_label() . '.' : ''),
        ]],
        'history'     => [
            ['time' => $now, 'text' => $assignee ? 'Hệ thống chuyển đến đầu mối ' . staff_name($assignee) : 'Phiếu vào hàng chờ phân loại'],
            ['time' => $now, 'text' => 'Phiếu được tạo' . ($anonymous ? ' (ẩn danh' . ($urgent ? ', khẩn' : '') . ')' : ($urgent ? ' (khẩn)' : ''))],
        ],
        'has_update'  => false,
        'revealed_to' => [],
    ];
    ticket_save($ticket);
    return $ticket;
}

/** Nhãn hạn xử lý: "Còn 25 phút", "Quá hạn 3 giờ" hoặc ngày giờ. Trả về [text, isAlert]. */
function deadline_info(array $t): array
{
    if (!$t['deadline'] || $t['status'] === 'Đã có phản hồi') {
        return ['—', false];
    }
    $diffMin = (int) floor((strtotime($t['deadline']) - time()) / 60);
    if ($diffMin < 0) return ['Quá hạn ' . human_minutes(-$diffMin), true];
    if ($diffMin <= 120) return ['Còn ' . human_minutes(max(1, $diffMin)), true];
    return [fmt_dt($t['deadline']), false];
}

/* ======================= Nhật ký ======================= */

function audit_all(): array
{
    return load('audit');
}

/** Ghi một dòng nhật ký thao tác. $actor = null nghĩa là "Hệ thống". */
function audit_log(string $action, ?string $ticketCode, string $detail, ?array $actor = null): void
{
    $log = load('audit');
    array_unshift($log, [
        'id'      => new_id('l'),
        'time'    => now_iso(),
        'actor'   => $actor['name'] ?? 'Hệ thống',
        'actor_id' => $actor['id'] ?? null,
        'action'  => $action,
        'ticket'  => $ticketCode,
        'detail'  => $detail,
    ]);
    save('audit', $log);
}
