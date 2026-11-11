<?php
declare(strict_types=1);

/** Người dùng đang đăng nhập (học sinh hoặc cán bộ) hoặc null. */
function current_user(): ?array
{
    static $user = false;
    if ($user === false) {
        $user = user_find($_SESSION['uid'] ?? null);
        if ($user && $user['locked']) {
            $user = null;
            unset($_SESSION['uid']);
        }
    }
    return $user;
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['uid'] = $user['id'];
}

function logout_user(): void
{
    $_SESSION = [];
    session_regenerate_id(true);
}

/* ======================= Chống dò (đăng nhập, mã tham gia) ======================= */

function client_ip(): string
{
    return (string) ($_SERVER['REMOTE_ADDR'] ?? 'cli');
}

/** Còn bị khóa bao nhiêu giây (0 = không khóa). $key vd. "login|email" hoặc "join|ip". */
function throttle_wait(string $key, int $max = THROTTLE_MAX): int
{
    $r = load('throttle')[hash('sha256', $key)] ?? null;
    if (!$r || $r['count'] < $max) return 0;
    return max(0, $r['first'] + THROTTLE_WINDOW - time());
}

function throttle_fail(string $key): void
{
    $all = array_filter(load('throttle'), fn ($r) => $r['first'] + THROTTLE_WINDOW > time()); // dọn bản ghi cũ
    $k = hash('sha256', $key);
    $r = $all[$k] ?? ['count' => 0, 'first' => time()];
    $r['count']++;
    $all[$k] = $r;
    save('throttle', $all);
}

function throttle_clear(string $key): void
{
    $all = load('throttle');
    unset($all[hash('sha256', $key)]);
    save('throttle', $all);
}

function throttle_message(int $wait): string
{
    return 'Bạn đã nhập sai quá nhiều lần. Thử lại sau ' . max(1, (int) ceil($wait / 60)) . ' phút.';
}

/**
 * Kiểm tra email + mật khẩu. Trả về [user|null, lỗi|null].
 * Sai 5 lần với một email → khóa email đó 15 phút; sai 30 lần từ một IP → khóa IP 15 phút.
 */
function attempt_login(string $email, string $password, string $role): array
{
    $keys = ['login|' . mb_strtolower(trim($email)), 'login-ip|' . client_ip()];
    if ($w = max(throttle_wait($keys[0]), throttle_wait($keys[1], THROTTLE_MAX_IP))) return [null, throttle_message($w)];
    $u = user_find_by_email($email);
    if (!$u || $u['role'] !== $role || !password_verify($password, $u['password'])) {
        foreach ($keys as $k) throttle_fail($k);
        return [null, 'Email hoặc mật khẩu không đúng.'];
    }
    throttle_clear($keys[0]);
    if ($u['locked']) {
        return [null, 'Tài khoản đã bị khóa. Liên hệ nhà trường.'];
    }
    if (!$u['verified']) {
        return [null, 'Tài khoản chưa xác thực email.'];
    }
    return [$u, null];
}

/* ======================= Vai trò & quyền ======================= */

function is_staff(?array $u): bool
{
    return $u && $u['role'] === 'staff';
}

function is_admin(?array $u): bool
{
    return is_staff($u) && $u['staff_role'] === ROLE_ADMIN;
}

/** Người được chỉ định mở danh tính (tối đa REVEAL_MAX người). */
function can_reveal(?array $u): bool
{
    return is_staff($u) && !empty($u['can_reveal']);
}

/** Được vào khu vực phiếu: cán bộ xử lý phiếu, hoặc người được chỉ định mở danh tính. */
function can_handle_tickets(?array $u): bool
{
    return is_staff($u) && ($u['staff_role'] === ROLE_HANDLER || can_reveal($u));
}

/** Được soạn học liệu, trả lời hỏi đáp: giáo viên (Quản lý học liệu) hoặc Quản trị viên. */
function can_manage_learning(?array $u): bool
{
    return is_staff($u) && in_array($u['staff_role'], [ROLE_TEACHER, ROLE_ADMIN], true);
}

/** Xem nhật ký thao tác: Quản trị viên hoặc người được chỉ định mở danh tính. */
function can_view_audit(?array $u): bool
{
    return is_admin($u) || can_reveal($u);
}

/** Cán bộ có thẩm quyền xem tệp gốc (còn GPS, thông tin máy): chỉ người được chỉ định mở danh tính. */
function can_view_original(?array $u): bool
{
    return can_reveal($u);
}

/** Id cán bộ trong một dòng bảng đầu mối. */
function routing_people(string $group): array
{
    $r = config()['routing'][$group] ?? [];
    return array_values(array_filter([$r['primary'] ?? '', $r['backup'] ?? '', $r['independent'] ?? '']));
}

/**
 * Cán bộ có được xem phiếu này không.
 * - Người được chỉ định mở danh tính: xem mọi phiếu (giám sát, tiếp nhận độc lập).
 * - Cán bộ xử lý phiếu: phiếu được phân công cho mình, hoặc nhóm mình là đầu mối / dự phòng / độc lập,
 *   hoặc phiếu đang ở hàng chờ phân loại nếu mình có tên ở dòng "Chưa rõ / Khác".
 * - Quản trị viên, giáo viên: không.
 */
function can_access_ticket(?array $u, array $t): bool
{
    if (!can_handle_tickets($u)) return false;
    if (can_reveal($u)) return true;
    if ($t['assignee'] === $u['id']) return true;
    if (in_array($u['id'], routing_people($t['group']), true)) return true;
    return $t['assignee'] === null && in_array($u['id'], routing_people(ISSUE_UNKNOWN), true);
}

/** Trang mặc định của cán bộ theo vai trò. */
function staff_home(array $u): string
{
    if (can_handle_tickets($u)) return 'qt/phieu.php';
    if (can_manage_learning($u)) return 'qt/hoc-lieu.php';
    return 'qt/tai-khoan.php';
}

/** Bắt buộc đăng nhập học sinh; nếu chưa thì chuyển tới trang đăng nhập. */
function require_student(): array
{
    $u = current_user();
    if (!$u || $u['role'] !== 'student') {
        $_SESSION['next'] = $_SERVER['REQUEST_URI'] ?? null;
        redirect('sv/dang-nhap.php');
    }
    return $u;
}

/**
 * Bắt buộc đăng nhập cán bộ.
 * $need: null (mọi cán bộ) | 'admin' | 'tickets' | 'learning' | 'audit'. true = 'admin' (tương thích cũ).
 */
function require_staff(bool|string|null $need = null): array
{
    $u = current_user();
    if (!is_staff($u)) {
        $_SESSION['next'] = $_SERVER['REQUEST_URI'] ?? null;
        redirect('qt/dang-nhap.php');
    }
    if ($need === true) $need = 'admin';
    $ok = match ($need) {
        null, false => true,
        'admin'     => is_admin($u),
        'tickets'   => can_handle_tickets($u),
        'learning'  => can_manage_learning($u),
        'audit'     => can_view_audit($u),
        default     => false,
    };
    if (!$ok) {
        flash('Vai trò của bạn không truy cập được trang này.', 'error');
        $home = staff_home($u);
        // Tránh vòng lặp chuyển hướng khi chính trang mặc định bị chặn
        redirect(str_ends_with((string) ($_SERVER['SCRIPT_NAME'] ?? ''), $home) ? 'qt/dang-nhap.php' : $home);
    }
    return $u;
}

/** Chuyển về trang đã định trước khi bị yêu cầu đăng nhập (chỉ đường dẫn nội bộ). */
function redirect_after_login(string $fallback): never
{
    $next = $_SESSION['next'] ?? null;
    unset($_SESSION['next']);
    if ($next && str_starts_with($next, BASE_URL . '/') && !str_starts_with($next, '//') && !str_contains($next, '\\')) {
        header('Location: ' . $next);
        exit;
    }
    redirect($fallback);
}
