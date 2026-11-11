<?php
declare(strict_types=1);

/* ================= Khung trang chung ================= */

function page_head(string $title, array $css = []): void
{
    $css = array_merge(['assets/css/app.css'], $css);
    ?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php if (SITE['noindex']): /* Bản chạy thử: không cho công cụ tìm kiếm lập chỉ mục, tránh bị nhầm là kênh chính thức */ ?>
<meta name="robots" content="noindex, nofollow">
<?php endif; ?>
<title><?= e($title) ?> – <?= e(SITE['project_name']) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&amp;display=swap">
<?php foreach ($css as $c): ?>
<link rel="stylesheet" href="<?= e(url($c)) ?>?v=3">
<?php endforeach; ?>
<script src="<?= e(url('assets/js/app.js')) ?>?v=1" defer></script>
</head>
<body>
<?php
    foreach (take_flashes() as $f) {
        echo '<div class="toast toast--' . e($f['type']) . '" role="status">' . e($f['msg']) . '</div>';
    }
}

function page_foot(): void
{
    echo "\n</body>\n</html>\n";
}

/* ================= Màn hình điện thoại (học sinh) ================= */

$GLOBALS['__mobile_footer_open'] = false;

/**
 * Mở khung màn hình học sinh.
 * $o: title, back (đường dẫn quay lại), no_header (bool)
 */
function mobile_start(string $title, array $o = []): void
{
    page_head($title);
    echo '<div class="m-shell"><div class="m-screen">';
    if (empty($o['no_header'])) {
        echo '<header class="m-header">';
        if (!empty($o['back'])) {
            echo '<a href="' . e($o['back']) . '" class="icon-btn" aria-label="Quay lại">' . icon('chevronLeft', 22) . '</a>';
        } else {
            echo '<span class="m-header__spacer"></span>';
        }
        echo '<h1 class="m-header__title">' . e($o['header'] ?? $title) . '</h1></header>';
    }
    echo '<main class="m-main">';
}

/** Kết thúc phần nội dung cuộn và mở thanh nút cố định ở cuối màn. */
function mobile_footer(string $class = 'm-footer'): void
{
    echo '</main><div class="' . e($class) . '">';
    $GLOBALS['__mobile_footer_open'] = true;
}

/** Đóng khung màn hình. $tab: Học | Gửi phiếu | Phiếu của tôi | Tài khoản */
function mobile_end(?string $tab = null): void
{
    echo $GLOBALS['__mobile_footer_open'] ? '</div>' : '</main>';
    if ($tab) {
        echo tab_bar($tab);
    }
    echo '</div></div>';
    page_foot();
}

function tab_bar(string $active): string
{
    $tabs = [
        ['Học', 'sv/hoc.php', 'book'],
        ['Gửi phiếu', 'sv/gui-phieu.php', 'send'],
        ['Phiếu của tôi', 'sv/phieu.php', 'inbox'],
        ['Tài khoản', 'sv/tai-khoan.php', 'user'],
    ];
    $h = '<nav class="tabbar" aria-label="Điều hướng chính">';
    foreach ($tabs as [$label, $href, $ic]) {
        $on = $label === $active;
        $h .= '<a href="' . e(url($href)) . '" class="tabbar__item' . ($on ? ' active' : '') . '"' . ($on ? ' aria-current="page"' : '') . '>'
            . icon($ic, 22) . '<span>' . e($label) . '</span></a>';
    }
    return $h . '</nav>';
}

function step_progress(int $step, int $total = 5): string
{
    $h = '<div class="stack gap-8"><div class="steps" aria-hidden="true">';
    for ($i = 1; $i <= $total; $i++) {
        $h .= '<span' . ($i <= $step ? ' class="on"' : '') . '></span>';
    }
    return $h . '</div><p class="t-small t-strong t-primary">Bước ' . $step . '/' . $total . '</p></div>';
}

/* ================= Trang quản trị (cán bộ) ================= */

/** [nhãn, đường dẫn, icon, quyền cần có – xem require_staff()] */
const ADMIN_MENU = [
    ['Phiếu phản ánh', 'qt/phieu.php', 'inbox', 'tickets'],
    ['Học liệu', 'qt/hoc-lieu.php', 'book', 'learning'],
    ['Hỏi đáp', 'qt/hoi-dap.php', 'messageLines', 'learning'],
    ['Cấu hình đầu mối', 'qt/cau-hinh.php', 'settings', 'admin'],
    ['Tài khoản', 'qt/tai-khoan.php', 'users', 'admin'],
    ['Mã tham gia', 'qt/ma-tham-gia.php', 'key', 'admin'],
    ['Nhật ký', 'qt/nhat-ky.php', 'history', 'audit'],
];

function staff_can(?array $u, string $need): bool
{
    return match ($need) {
        'admin'    => is_admin($u),
        'tickets'  => can_handle_tickets($u),
        'learning' => can_manage_learning($u),
        'audit'    => can_view_audit($u),
        default    => false,
    };
}

function admin_start(string $section, string $title, string $subtitle = '', string $actions = ''): void
{
    $me = current_user();
    page_head($title);
    ?>
<div class="a-shell">
  <div class="a-sidebar-col">
    <nav class="sidebar" aria-label="Menu quản trị">
      <div class="sidebar__brand">
        <div class="stack gap-2">
          <?= wordmark('on-dark wordmark--sm') ?>
          <p class="t-small sidebar__brand-text" style="opacity:.8">Trang quản trị</p>
        </div>
      </div>
      <div class="sidebar__nav">
      <?php foreach (ADMIN_MENU as [$label, $href, $ic, $need]):
          if (!staff_can($me, $need)) continue;
          $on = $label === $section; ?>
        <a href="<?= e(url($href)) ?>" class="sidebar__link<?= $on ? ' active' : '' ?>"<?= $on ? ' aria-current="page"' : '' ?>><?= icon($ic, 20, '#fff') ?><span><?= e($label) ?></span></a>
      <?php endforeach; ?>
      </div>
      <div class="sidebar__user">
        <span class="sidebar__avatar" aria-hidden="true"><?= e(mb_substr($me['name'] ?? '?', -1)) ?></span>
        <div class="sidebar__user-text grow">
          <p class="t-small t-strong"><?= e($me['name'] ?? '') ?></p>
          <p class="t-small" style="opacity:.8"><?= e($me['staff_role'] ?? '') ?></p>
        </div>
        <form method="post" action="<?= e(url('dang-xuat.php')) ?>" class="inline-form">
          <?= csrf_field() ?>
          <button type="submit" class="icon-btn icon-btn--on-dark" aria-label="Đăng xuất"><?= icon('logout', 18, '#fff') ?></button>
        </form>
      </div>
    </nav>
  </div>
  <div class="a-content">
    <header class="a-header">
      <div class="a-header__text">
        <h1 class="t-h1"><?= e($title) ?></h1>
        <?php if ($subtitle !== ''): ?><p class="t-small t-muted"><?= e($subtitle) ?></p><?php endif; ?>
      </div>
      <?= $actions ?>
    </header>
    <main class="a-main">
<?php
}

function admin_end(): void
{
    echo "\n    </main>\n  </div>\n</div>\n";
    page_foot();
}
