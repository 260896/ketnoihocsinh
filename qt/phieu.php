<?php
/* QT02 – Danh sách phiếu */
require __DIR__ . '/../includes/bootstrap.php';
$me = require_staff('tickets');

const PAGE_SIZE = 10;
// Chỉ phiếu cán bộ này được xem (nhóm được giao / được phân công / người được chỉ định)
$all = array_values(array_filter(tickets_all(), fn ($t) => can_access_ticket($me, $t)));

$quickDefs = [
    'all'     => ['Tất cả', false, fn ($t) => true],
    'queue'   => ['Hàng chờ phân loại', false, fn ($t) => $t['assignee'] === null],
    'urgent'  => ['Cần phản hồi ngay', true, fn ($t) => $t['priority'] === PRIORITY_NOW && $t['status'] !== 'Đã có phản hồi'],
    'overdue' => ['Quá hạn', true, fn ($t) => str_starts_with(deadline_info($t)[0], 'Quá hạn')],
    'mine'    => ['Của tôi', false, fn ($t) => $t['assignee'] === $me['id']],
];
$quick = array_key_exists(get('nhanh'), $quickDefs) ? get('nhanh') : 'all';

$f = [
    'q' => get('q'), 'group' => get('nhom'), 'priority' => get('uu-tien'),
    'status' => get('trang-thai'), 'deadline' => get('han'), 'assignee' => get('phu-trach'),
];

$rows = array_filter($all, function ($t) use ($quickDefs, $quick, $f) {
    if (!$quickDefs[$quick][2]($t)) return false;
    if ($f['q'] !== '' && !str_contains(mb_strtolower($t['code'] . ' ' . $t['type'] . ' ' . $t['group'] . ' ' . $t['description']), mb_strtolower(ltrim($f['q'], '#')))) return false;
    if ($f['group'] !== '' && $t['group'] !== $f['group']) return false;
    if ($f['priority'] !== '' && $t['priority'] !== $f['priority']) return false;
    if ($f['status'] !== '' && $t['status'] !== $f['status']) return false;
    [$dl] = deadline_info($t);
    if ($f['deadline'] === 'qua-han' && !str_starts_with($dl, 'Quá hạn')) return false;
    if ($f['deadline'] === 'sap-den' && !str_starts_with($dl, 'Còn')) return false;
    if ($f['assignee'] === 'queue' && $t['assignee'] !== null) return false;
    if ($f['assignee'] !== '' && $f['assignee'] !== 'queue' && $t['assignee'] !== $f['assignee']) return false;
    return true;
});
$rows = array_values($rows);
$total = count($rows);
$pages = max(1, (int) ceil($total / PAGE_SIZE));
$page = min($pages, max(1, (int) get('trang', '1')));
$shown = array_slice($rows, ($page - 1) * PAGE_SIZE, PAGE_SIZE);
$qs = fn (array $extra) => url('qt/phieu.php', array_merge($_GET, $extra));

$staffOpts = ['' => 'Tất cả', 'queue' => 'Hàng chờ phân loại'] + array_column(array_filter(staff_all(), 'can_handle_tickets'), 'name', 'id');

admin_start('Phiếu phản ánh', 'Phiếu phản ánh', can_reveal($me)
    ? 'Bạn là người được chỉ định: xem được mọi phiếu để giám sát và tiếp nhận độc lập.'
    : 'Chỉ hiện phiếu thuộc nhóm bạn phụ trách / dự phòng / được phân công.');
?>
<div class="row wrap gap-8">
  <?php foreach ($quickDefs as $k => [$label, $danger, $test]): ?>
    <a href="<?= e(url('qt/phieu.php', ['nhanh' => $k])) ?>" class="chip<?= $danger ? ' chip--danger' : '' ?><?= $quick === $k ? ' chip--active' : '' ?>"<?= $quick === $k ? ' aria-current="true"' : '' ?>>
      <?= e($label) ?> · <?= count(array_filter($all, $test)) ?>
    </a>
  <?php endforeach; ?>
</div>

<form method="get" class="card card--admin" style="padding:16px">
  <input type="hidden" name="nhanh" value="<?= e($quick) ?>">
  <div class="filter-grid">
    <?= field_input('q', 'Tìm kiếm', $f['q'], ['placeholder' => 'Mã phiếu, từ khóa']) ?>
    <?= field_select('nhom', 'Nhóm vấn đề', ['' => 'Tất cả'] + array_combine([...issue_groups(), ISSUE_UNKNOWN], [...issue_groups(), ISSUE_UNKNOWN]), $f['group']) ?>
    <?= field_select('uu-tien', 'Mức ưu tiên', ['' => 'Tất cả'] + array_combine(PRIORITIES, PRIORITIES), $f['priority']) ?>
    <?= field_select('trang-thai', 'Trạng thái', ['' => 'Tất cả'] + array_combine(TICKET_STATUSES, TICKET_STATUSES), $f['status']) ?>
    <?= field_select('han', 'Hạn xử lý', ['' => 'Tất cả', 'sap-den' => 'Sắp đến hạn', 'qua-han' => 'Quá hạn'], $f['deadline']) ?>
    <?= field_select('phu-trach', 'Người phụ trách', $staffOpts, $f['assignee']) ?>
  </div>
  <div class="row gap-10">
    <button type="submit" class="btn btn--sm"><?= icon('search', 16) ?> Lọc</button>
    <a href="<?= e(url('qt/phieu.php')) ?>" class="btn btn--sm btn--ghost">Xóa lọc</a>
  </div>
</form>

<div class="table-wrap">
  <table class="table">
    <thead><tr>
      <th scope="col" style="width:130px">Mã phiếu</th>
      <th scope="col">Loại · Nhóm vấn đề</th>
      <th scope="col" style="width:90px">Ưu tiên</th>
      <th scope="col" style="width:140px">Trạng thái</th>
      <th scope="col" style="width:150px">Hạn xử lý</th>
      <th scope="col" style="width:170px">Phụ trách</th>
      <th scope="col" style="width:110px">Danh tính</th>
    </tr></thead>
    <tbody>
    <?php foreach ($shown as $t): [$dl, $alert] = deadline_info($t); ?>
      <tr<?= $alert ? ' class="is-alert"' : '' ?>>
        <td class="t-nowrap"><a href="<?= e(url('qt/xu-ly-phieu.php', ['id' => $t['code']])) ?>" style="text-decoration:none">#<?= e($t['code']) ?></a></td>
        <td><?= e($t['type']) ?> · <?= e($t['group'] === ISSUE_UNKNOWN ? 'Chưa rõ nhóm' : $t['group']) ?><?php if (!empty($t['title'])): ?><br><span class="t-small t-muted"><?= e($t['title']) ?></span><?php endif; ?></td>
        <td><?= priority_badge($t['priority']) ?></td>
        <td><?= status_badge($t['status']) ?></td>
        <td><?= $alert ? '<span class="t-danger t-strong">' . e($dl) . '</span>' : e($dl) ?></td>
        <td><?= $t['assignee'] ? e(staff_name($t['assignee'])) : (!empty($t['needs_independent']) ? '<span class="t-danger t-strong">Chờ chỉ định độc lập</span>' : '<span class="t-muted">Hàng chờ phân loại</span>') ?></td>
        <td><?= $t['anonymous'] ? 'Ẩn danh' : 'Có danh tính' ?></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$shown): ?><tr><td colspan="7" class="table-empty">Không có phiếu phù hợp bộ lọc.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<div class="row gap-8">
  <p class="t-small t-muted grow">Hiển thị <?= $total ? ($page - 1) * PAGE_SIZE + 1 : 0 ?>–<?= min($total, $page * PAGE_SIZE) ?> / <?= $total ?> phiếu</p>
  <a href="<?= e($qs(['trang' => $page - 1])) ?>" class="btn btn--outline btn--sm<?= $page <= 1 ? ' is-disabled' : '' ?>">Trang trước</a>
  <a href="<?= e($qs(['trang' => $page + 1])) ?>" class="btn btn--outline btn--sm<?= $page >= $pages ? ' is-disabled' : '' ?>">Trang sau</a>
</div>
<?php admin_end();
