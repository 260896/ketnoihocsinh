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

/** Kiểm tra email + mật khẩu. Trả về [user|null, lỗi|null]. */
function attempt_login(string $email, string $password, string $role): array
{
    $u = user_find_by_email($email);
    if (!$u || $u['role'] !== $role || !password_verify($password, $u['password'])) {
        return [null, 'Email hoặc mật khẩu không đúng.'];
    }
    if ($u['locked']) {
        return [null, 'Tài khoản đã bị khóa. Liên hệ nhà trường.'];
    }
    if (!$u['verified']) {
        return [null, 'Tài khoản chưa xác thực email.'];
    }
    return [$u, null];
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

/** Bắt buộc đăng nhập cán bộ. $adminOnly = chỉ Quản trị viên. */
function require_staff(bool $adminOnly = false): array
{
    $u = current_user();
    if (!$u || $u['role'] !== 'staff') {
        $_SESSION['next'] = $_SERVER['REQUEST_URI'] ?? null;
        redirect('qt/dang-nhap.php');
    }
    if ($adminOnly && $u['staff_role'] !== 'Quản trị viên') {
        flash('Chỉ Quản trị viên mới truy cập được trang này.', 'error');
        redirect('qt/phieu.php');
    }
    return $u;
}

function is_admin(?array $u): bool
{
    return $u && $u['role'] === 'staff' && $u['staff_role'] === 'Quản trị viên';
}

/** Cán bộ có thẩm quyền xem tệp gốc (còn GPS, thông tin máy): người được chỉ định hoặc Quản trị viên. */
function can_view_original(?array $u): bool
{
    return $u && $u['role'] === 'staff' && (!empty($u['can_reveal']) || $u['staff_role'] === 'Quản trị viên');
}

/** Chuyển về trang đã định trước khi bị yêu cầu đăng nhập. */
function redirect_after_login(string $fallback): never
{
    $next = $_SESSION['next'] ?? null;
    unset($_SESSION['next']);
    if ($next && str_starts_with($next, BASE_URL . '/')) {
        header('Location: ' . $next);
        exit;
    }
    redirect($fallback);
}
