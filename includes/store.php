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
/*
 * TÁCH DỮ LIỆU (CLAUDE.md 4.1):
 *  - data/tickets.json       nội dung phiếu – KHÔNG chứa id tài khoản người gửi.
 *  - data/ticket_owners.json liên kết mã phiếu → id tài khoản, chỉ đọc qua ticket_owner_id().
 * Màn hình cán bộ chỉ tra liên kết này khi phiếu có danh tính hoặc người xem đã mở danh tính.
 */

/** Chuyển dữ liệu kiểu cũ (owner_id nằm trong phiếu, ưu tiên 3 mức) sang kiểu mới. Chạy 1 lần. */
function tickets_upgrade(): void
{
    static $done = false;
    if ($done) return;
    $done = true;
    $all = load('tickets');
    $owners = load('ticket_owners');
    $changed = false;
    foreach ($all as &$t) {
        if (array_key_exists('owner_id', $t)) {
            if ($t['owner_id']) $owners[$t['code']] = $t['owner_id'];
            unset($t['owner_id']);
            $changed = true;
        }
        foreach ($t['messages'] as &$m) {
            if (($m['from'] ?? '') === 'student' && array_key_exists('author_id', $m)) {
                unset($m['author_id']);
                $changed = true;
            }
        }
        unset($m);
        if (!array_key_exists('conflict', $t)) {
            $t += ['conflict' => [], 'needs_independent' => false, 'title' => ''];
            $changed = true;
        }
        if (!in_array($t['priority'], PRIORITIES, true)) {
            $t['priority'] = in_array($t['priority'], ['Khẩn', 'Cao'], true) || !empty($t['urgent']) ? PRIORITY_NOW : PRIORITY_LATER;
            $changed = true;
        }
    }
    unset($t);
    if ($changed) {
        save('ticket_owners', $owners);
        save('tickets', $all);
    }
}

function tickets_all(): array
{
    tickets_upgrade();
    $t = load('tickets');
    usort($t, fn ($a, $b) => strcmp($b['created_at'], $a['created_at']));
    return $t;
}

function ticket_find(string $code): ?array
{
    tickets_upgrade();
    foreach (load('tickets') as $t) {
        if ($t['code'] === $code) return $t;
    }
    return null;
}

function ticket_save(array $ticket): void
{
    unset($ticket['owner_id']); // không bao giờ ghi id người gửi vào phiếu
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

/** Id tài khoản đã gửi phiếu (null nếu không có). */
function ticket_owner_id(string $code): ?string
{
    tickets_upgrade();
    return load('ticket_owners')[$code] ?? null;
}

function ticket_is_owned_by(array $t, array $user): bool
{
    return ticket_owner_id($t['code']) === $user['id'];
}

/** Phiếu của một học sinh, mới nhất trước. */
function tickets_of_user(string $userId): array
{
    $codes = array_keys(array_filter(load('ticket_owners'), fn ($uid) => $uid === $userId));
    return array_values(array_filter(tickets_all(), fn ($t) => in_array($t['code'], $codes, true)));
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
 * nhóm chưa có đầu mối hoặc "Chưa rõ" vào hàng chờ phân loại. Phiếu khẩn có hạn xác nhận ngắn.
 */
function ticket_create(array $owner, array $draft): array
{
    tickets_upgrade();
    $cfg = config();
    $urgent = $draft['urgent'] === '1';
    $anonymous = $draft['anonymous'] !== '0';
    $route = $cfg['routing'][$draft['group']] ?? ['primary' => ''];
    $assignee = ($route['primary'] ?? '') ?: null;
    $absentNote = '';
    // Người chính vắng mặt → người dự phòng (nếu có và không vắng); không có ai → hàng chờ + báo quản trị viên
    if ($assignee && staff_is_absent($assignee)) {
        $backup = ($route['backup'] ?? '') ?: null;
        $absentNote = staff_name($assignee) . ' vắng mặt';
        $assignee = $backup && !staff_is_absent($backup) ? $backup : null;
    }
    $deadline = date('c', ticket_deadline(time(), $urgent));
    $now = now_iso();
    $code = next_ticket_code();

    $ticket = [
        'code'        => $code,
        'type'        => $draft['type'],
        'title'       => $draft['title'] ?? '',
        'group'       => $draft['group'],
        'conflict'    => [],
        'needs_independent' => false,
        'urgent'      => $urgent,
        'anonymous'   => $anonymous,
        'status'      => 'Đã tiếp nhận',
        'priority'    => $urgent ? PRIORITY_NOW : PRIORITY_LATER,
        'created_at'  => $now,
        'deadline'    => $deadline,
        'assignee'    => $assignee,
        'acknowledged_at' => null,
        'escalated_at'    => null,
        'reminded_at'     => null,
        'description' => $draft['description'],
        'occurred_at' => $draft['occurred_at'] ? date('c', (int) strtotime($draft['occurred_at'])) : '',
        'location'    => $draft['location'],
        'attachments' => $draft['files'],
        'messages'    => [[
            'from' => 'system', 'author_id' => null, 'time' => $now,
            'text' => $urgent
                ? 'Tin nhắn tự động: hệ thống đã tiếp nhận phản ánh khẩn của em và báo cho cán bộ phụ trách. Đây chưa phải phản hồi của cán bộ.'
                : 'Tin nhắn tự động: hệ thống đã tiếp nhận phản ánh. Đây chưa phải phản hồi của cán bộ.'
                  . (is_out_of_hours() ? ' Phản ánh được gửi ngoài giờ làm việc, dự kiến được xử lý từ ' . expected_processing_label() . '.' : ''),
        ]],
        'history'     => [
            ['time' => $now, 'text' => ($absentNote ? $absentNote . ' → ' : '') . ($assignee ? 'Hệ thống chuyển đến ' . staff_name($assignee) : 'Phiếu vào hàng chờ phân loại')],
            ['time' => $now, 'text' => 'Phiếu được tạo' . ($anonymous ? ' (ẩn danh' . ($urgent ? ', cần phản hồi ngay' : '') . ')' : ($urgent ? ' (cần phản hồi ngay)' : ''))],
        ],
        'has_update'  => false,
        'revealed_to' => [],
    ];
    ticket_save($ticket);
    $owners = load('ticket_owners');
    $owners[$code] = $owner['id'];
    save('ticket_owners', $owners);

    // Báo cán bộ (không kèm nội dung phiếu). Brief 2.8: phiếu khẩn gửi email ngay cho đầu mối VÀ người dự phòng.
    $to = $assignee ? [$assignee] : routing_people(ISSUE_UNKNOWN);
    if ($urgent && !empty($route['backup'])) $to[] = $route['backup'];
    if (!$assignee) $to = [...$to, ...array_column(array_filter(staff_all(), fn ($u) => $u['staff_role'] === ROLE_ADMIN), 'id')];
    notify_staff($to, $ticket, $urgent ? 'Phiếu CẦN PHẢN HỒI NGAY mới' : 'Phiếu mới');
    return $ticket;
}

/**
 * Hạn xử lý theo hồ sơ mục 3:
 * - Cần phản hồi ngay: xác nhận trong N phút, tính liên tục trong giờ làm việc; nhận ngoài giờ thì tính từ đầu giờ làm việc kế tiếp
 *   (không có trực thực tế ngoài giờ – không hiển thị như cam kết hỗ trợ 24/7).
 * - Thường: cùng thời điểm sau N ngày làm việc; nhận ngoài giờ thì mốc bắt đầu là đầu giờ làm việc kế tiếp.
 *   Ví dụ: thứ Sáu 13/11 18:00 → bắt đầu thứ Hai 16/11 08:00 → hạn thứ Tư 18/11 08:00.
 */
function ticket_deadline(int $receivedTs, bool $urgent): int
{
    $cfg = config();
    $start = next_duty_start($receivedTs);
    return $urgent
        ? $start + (int) $cfg['urgent_ack_minutes'] * 60
        : add_working_days($start, (int) ($cfg['first_reply_days'] ?? 2));
}

/** Cán bộ đang vắng mặt (quản trị viên bật ở trang Tài khoản). */
function staff_is_absent(?string $id): bool
{
    return $id !== null && !empty(user_find($id)['absent']);
}

/**
 * Đánh dấu cán bộ có liên quan (xung đột lợi ích) – hồ sơ mục 2:
 * người liên quan bị loại khỏi phiếu; nếu đang phụ trách thì chuyển người tiếp nhận độc lập;
 * nếu người độc lập cũng liên quan thì bỏ trống và đặt "chờ chỉ định người độc lập khác" – không trả về người bị phản ánh.
 */
function ticket_set_conflict(array $t, array $staffIds, array $by, string $reason = ''): array
{
    $staffIds = array_values(array_unique(array_filter($staffIds)));
    $t['conflict'] = $staffIds;
    if ($t['assignee'] && in_array($t['assignee'], $staffIds, true)) {
        $old = $t['assignee'];
        $indep = config()['routing'][$t['group']]['independent'] ?? '';
        $t['assignee'] = $indep && !in_array($indep, $staffIds, true) && !staff_is_absent($indep) ? $indep : null;
        $t['needs_independent'] = $t['assignee'] === null;
        array_unshift($t['history'], ['time' => now_iso(), 'text' => 'Xung đột lợi ích: chuyển khỏi ' . staff_name($old) . ' → ' . ($t['assignee'] ? staff_name($t['assignee']) : 'chờ chỉ định người độc lập khác')]);
        audit_log('Phân công', $t['code'], 'Chuyển do xung đột lợi ích: ' . staff_name($old) . ' → ' . ($t['assignee'] ? staff_name($t['assignee']) : 'chờ chỉ định độc lập') . ($reason !== '' ? ' · Lý do: ' . $reason : ''), $by);
        if ($t['assignee']) notify_staff([$t['assignee']], $t, 'Phiếu chuyển cho bạn (tiếp nhận độc lập)');
    }
    return $t;
}

/** Cán bộ xác nhận đã nhận phiếu khẩn (hoặc đã trả lời / đổi trạng thái): dừng đồng hồ 15 phút. */
function ticket_acknowledge(array $t, array $by): array
{
    if (!empty($t['acknowledged_at'])) return $t;
    $t['acknowledged_at'] = now_iso();
    if ($t['urgent']) {
        // Sau khi xác nhận, hạn phản hồi nội dung là 1 ngày làm việc
        $t['deadline'] = date('c', add_working_days(time(), 1));
    }
    array_unshift($t['history'], ['time' => now_iso(), 'text' => $by['name'] . ' xác nhận đã nhận phiếu']);
    audit_log('Xác nhận', $t['code'], 'Xác nhận đã nhận phiếu', $by);
    return $t;
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

/**
 * Phiếu có thể trùng / liên quan (CLAUDE.md 4.8): chỉ GỢI Ý, không tự đóng / xóa.
 * Cùng nhóm, gửi cách nhau ≤ 14 ngày, mô tả có ≥ 3 từ (≥ 4 ký tự) giống nhau.
 */
function ticket_similar(array $t, int $limit = 3): array
{
    $words = fn (string $s) => array_unique(array_filter(preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($s)), fn ($w) => mb_strlen($w) >= 4));
    $mine = $words($t['description'] . ' ' . $t['location']);
    $out = [];
    foreach (tickets_all() as $o) {
        if ($o['code'] === $t['code'] || $o['group'] !== $t['group']) continue;
        if (abs(strtotime($o['created_at']) - strtotime($t['created_at'])) > 14 * 86400) continue;
        $common = count(array_intersect($mine, $words($o['description'] . ' ' . $o['location'])));
        if ($common >= 3) $out[] = ['ticket' => $o, 'score' => $common];
    }
    usort($out, fn ($a, $b) => $b['score'] <=> $a['score']);
    return array_slice($out, 0, $limit);
}

/* ======================= Email / thông báo ra ngoài ======================= */
/*
 * Mọi thư đều đi qua outbox (data/outbox.json) để quản trị viên kiểm tra được.
 * Nếu config 'mail_enabled' = true và PHP đã cấu hình gửi thư thì gửi thật bằng mail().
 * Nội dung thư KHÔNG BAO GIỜ chứa nội dung phiếu (CLAUDE.md 4.5): chỉ mã phiếu và đường dẫn đăng nhập.
 */

function mail_send(string $to, string $subject, string $body, ?string $ticketCode = null): void
{
    $sent = false;
    if (!empty(config()['mail_enabled']) && function_exists('mail')) {
        $headers = "Content-Type: text/plain; charset=UTF-8\r\nFrom: no-reply@" . SITE['domain'];
        $sent = @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, $headers);
    }
    $box = load('outbox');
    array_unshift($box, [
        'id' => new_id('m'), 'time' => now_iso(), 'to' => $to, 'subject' => $subject,
        'body' => $body, 'ticket' => $ticketCode, 'sent' => $sent,
    ]);
    save('outbox', array_slice($box, 0, 500));
}

function site_link(string $path): string
{
    $host = $_SERVER['HTTP_HOST'] ?? SITE['domain'];
    return 'http://' . $host . url($path);
}

/** Báo học sinh: chỉ "Phiếu có cập nhật". */
function notify_student_update(array $t): void
{
    $u = user_find(ticket_owner_id($t['code']));
    if (!$u) return;
    mail_send($u['email'], 'Phiếu #' . $t['code'] . ' có cập nhật',
        "Phiếu #{$t['code']} của em có cập nhật.\nĐăng nhập để xem: " . site_link('sv/phieu.php') . "\n\nThư tự động – không trả lời thư này.", $t['code']);
}

/** Thư xác thực email khi đăng ký (hiệu lực 24 giờ). */
function send_verify_mail(array $u): void
{
    mail_send($u['email'], 'Xác thực email – ' . SITE['project_name'],
        "Chào {$u['name']},\nMở liên kết sau để kích hoạt tài khoản (hiệu lực " . SITE['verify_ttl'] . "): " .
        site_link('sv/xac-thuc.php?token=' . $u['verify_token']) . "\nNếu bạn không đăng ký, hãy bỏ qua thư này.");
}

/** Báo cán bộ: mã phiếu, mức ưu tiên, hạn xử lý – không có mô tả, tên người gửi, nhóm vấn đề. */
function notify_staff(array $userIds, array $t, string $reason): void
{
    foreach (array_unique(array_filter($userIds)) as $id) {
        $u = user_find($id);
        if (!$u || $u['locked']) continue;
        mail_send($u['email'], $reason . ' – #' . $t['code'],
            "$reason: #{$t['code']}\nMức ưu tiên: {$t['priority']}\nHạn: " . fmt_dt($t['deadline']) .
            "\nĐăng nhập trang quản trị để xem: " . site_link('qt/xu-ly-phieu.php?id=' . rawurlencode($t['code'])) .
            "\n\nThư tự động – không chứa nội dung phiếu.", $t['code']);
    }
}

/* ======================= Nhắc hạn & chuyển dự phòng (SLA) ======================= */

/**
 * Chạy định kỳ: bằng cron (php cron.php mỗi phút) và dự phòng ở mỗi lượt truy cập (tối đa 1 lần/phút).
 * - Phiếu cần phản hồi ngay, quá hạn xác nhận: chuyển người dự phòng (nếu bật) + nhắc cả hai; không có dự phòng → nhắc đầu mối + quản trị viên.
 * - Phiếu thường quá hạn phản hồi lần đầu: nhắc người phụ trách 1 lần.
 * Không phiếu nào bị tự đóng hay xóa.
 */
function sla_run(bool $force = false): int
{
    $state = load('sla');
    if (!$force && time() - (int) ($state['last_run'] ?? 0) < 60) return 0;
    save('sla', ['last_run' => time()]);

    $cfg = config();
    $n = 0;
    foreach (tickets_all() as $t) {
        if ($t['status'] === 'Đã có phản hồi' || !$t['deadline'] || strtotime($t['deadline']) > time()) continue;

        if ($t['urgent'] && empty($t['acknowledged_at']) && empty($t['escalated_at'])) {
            $backup = $cfg['routing'][$t['group']]['backup'] ?? '';
            if ($backup && (staff_is_absent($backup) || in_array($backup, $t['conflict'] ?? [], true))) $backup = '';
            $old = $t['assignee'];
            $t['escalated_at'] = now_iso();
            if (!empty($cfg['fallback']) && $backup && $backup !== $old) {
                $t['assignee'] = $backup;
                $t['deadline'] = date('c', time() + (int) $cfg['urgent_ack_minutes'] * 60);
                array_unshift($t['history'], ['time' => now_iso(), 'text' => 'Quá hạn xác nhận ' . $cfg['urgent_ack_minutes'] . ' phút → hệ thống chuyển người dự phòng ' . staff_name($backup)]);
                audit_log('Chuyển dự phòng', $t['code'], staff_name($old) . ' → ' . staff_name($backup) . ' (quá hạn xác nhận)', null);
                notify_staff([$backup], $t, 'Phiếu khẩn chuyển cho bạn (quá hạn xác nhận)');
                notify_staff([$old], $t, 'Nhắc: phiếu khẩn quá hạn xác nhận, đã chuyển dự phòng');
            } else {
                $admins = array_column(array_filter(staff_all(), fn ($u) => $u['staff_role'] === ROLE_ADMIN), 'id');
                array_unshift($t['history'], ['time' => now_iso(), 'text' => 'Quá hạn xác nhận – chưa có người dự phòng, đã nhắc đầu mối và quản trị viên']);
                audit_log('Nhắc hạn', $t['code'], 'Phiếu khẩn quá hạn xác nhận, không có người dự phòng', null);
                notify_staff([$old ?? '', ...$admins, ...routing_people(ISSUE_UNKNOWN)], $t, 'Nhắc: phiếu khẩn quá hạn xác nhận');
            }
            ticket_save($t);
            $n++;
        } elseif (!$t['urgent'] && empty($t['reminded_at'])) {
            $t['reminded_at'] = now_iso();
            array_unshift($t['history'], ['time' => now_iso(), 'text' => 'Quá hạn phản hồi lần đầu – hệ thống gửi nhắc']);
            audit_log('Nhắc hạn', $t['code'], 'Quá hạn phản hồi lần đầu', null);
            notify_staff($t['assignee'] ? [$t['assignee']] : routing_people(ISSUE_UNKNOWN), $t, 'Nhắc: phiếu quá hạn phản hồi lần đầu');
            ticket_save($t);
            $n++;
        }
    }
    return $n;
}

/* ======================= Hỏi đáp & "Vì sao em chọn" ======================= */
/*
 * data/questions.json. Mỗi mục:
 *  kind 'question' – câu hỏi của học sinh về một chủ đề; giáo viên trả lời, có thể công bố (ẩn tên) cho cả lớp xem.
 *  kind 'why'      – câu trả lời mở "Vì sao em chọn" của một tình huống; chỉ giáo viên và chính học sinh đó xem.
 */

function questions_all(): array
{
    $q = load('questions');
    usort($q, fn ($a, $b) => strcmp($b['created_at'], $a['created_at']));
    return $q;
}

function question_save(array $q): void
{
    $all = load('questions');
    $found = false;
    foreach ($all as &$x) {
        if ($x['id'] === $q['id']) { $x = $q; $found = true; }
    }
    unset($x);
    if (!$found) $all[] = $q;
    save('questions', $all);
}

function question_find(string $id): ?array
{
    foreach (load('questions') as $q) if ($q['id'] === $id) return $q;
    return null;
}

/** Câu "Vì sao em chọn" của học sinh cho 1 tình huống (bản mới nhất). */
function why_answer_of(string $userId, string $situationId): ?array
{
    foreach (questions_all() as $q) {
        if ($q['kind'] === 'why' && $q['user_id'] === $userId && $q['situation_id'] === $situationId) return $q;
    }
    return null;
}

/* ======================= Huy hiệu học tập ======================= */
/* Chỉ gắn với việc HỌC (CLAUDE.md 4.9) – không gắn với gửi phản ánh. Tính từ tiến độ, không lưu riêng. */

/**
 * Kết quả học một chủ đề: [số câu đã làm, số câu đúng (lần chọn gần nhất), tổng, đạt?].
 * Đạt khi đúng ≥ pass_threshold câu (hồ sơ mục 7: 2/3), hoặc ≥ 2/3 số câu nếu chủ đề có số câu khác 3.
 */
function learning_result(array $user, array $topic): array
{
    $list = published_situations($topic);
    $ids = array_column($list, 'id');
    $done = count(array_intersect($ids, $user['progress'][$topic['id']] ?? []));
    $correct = count(array_filter($ids, fn ($id) => !empty($user['answers'][$id])));
    $total = count($list);
    $need = $total === 3 ? (int) (config()['pass_threshold'] ?? 2) : (int) ceil($total * 2 / 3);
    return [$done, $correct, $total, $total > 0 && $done >= $total && $correct >= $need, $need];
}

function learning_badges(array $user): array
{
    $badges = [];
    $doneTopics = 0;
    $topics = array_values(array_filter(topics_all(), fn ($t) => published_situations($t)));
    foreach ($topics as $t) {
        if (learning_result($user, $t)[3]) {
            $doneTopics++;
            $badges[] = ['icon' => $t['icon'], 'name' => 'Hoàn thành: ' . $t['name']];
        }
    }
    if ($topics && $doneTopics === count($topics)) {
        array_unshift($badges, ['icon' => 'award', 'name' => 'Hoàn thành tất cả ' . count($topics) . ' chủ đề']);
    }
    return $badges;
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
