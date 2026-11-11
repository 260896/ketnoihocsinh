<?php
/* QT09 – Nhật ký thao tác (+ xuất CSV: ?xuat=csv) */
require __DIR__ . '/../includes/bootstrap.php';
$me = require_staff('audit');
$tab = get('tab') === 'email' ? 'email' : 'log';

$log = audit_all();
$f = ['from' => get('tu'), 'to' => get('den'), 'action' => get('hanh-dong'), 'actor' => get('nguoi'), 'ticket' => mb_strtolower(ltrim(get('phieu'), '#'))];

$rows = array_values(array_filter($log, function ($r) use ($f) {
    $day = date('Y-m-d', strtotime($r['time']));
    if ($f['from'] !== '' && $day < $f['from']) return false;
    if ($f['to'] !== '' && $day > $f['to']) return false;
    if ($f['action'] !== '' && $r['action'] !== $f['action']) return false;
    if ($f['actor'] !== '' && $r['actor'] !== $f['actor']) return false;
    if ($f['ticket'] !== '' && !str_contains(mb_strtolower((string) $r['ticket']), $f['ticket'])) return false;
    return true;
}));

if (get('xuat') === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="nhat-ky-thao-tac-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // BOM để Excel đọc đúng tiếng Việt
    fputcsv($out, ['Thời gian', 'Người thực hiện', 'Hành động', 'Phiếu', 'Chi tiết']);
    foreach ($rows as $r) {
        fputcsv($out, [fmt_dt($r['time']), $r['actor'], $r['action'], $r['ticket'] ? '#' . $r['ticket'] : '—', $r['detail']]);
    }
    exit;
}

$actions = array_values(array_unique(array_column($log, 'action')));
$actors = array_values(array_unique(array_column($log, 'actor')));
$sensitive = ['Mở danh tính', 'Tải tệp gốc'];

admin_start('Nhật ký', 'Nhật ký thao tác', 'Ghi lại phân loại, phân công, phản hồi, mở danh tính, tải tệp gốc và email hệ thống gửi đi.',
    $tab === 'log' ? '<a href="' . e(url('qt/nhat-ky.php', $_GET + ['xuat' => 'csv'])) . '" class="btn btn--outline">' . icon('download', 18) . ' Xuất CSV</a>' : '');
?>
<div class="row wrap gap-8">
  <a href="<?= e(url('qt/nhat-ky.php')) ?>" class="chip<?= $tab === 'log' ? ' chip--active' : '' ?>">Thao tác · <?= count($log) ?></a>
  <a href="<?= e(url('qt/nhat-ky.php', ['tab' => 'email'])) ?>" class="chip<?= $tab === 'email' ? ' chip--active' : '' ?>">Email đi · <?= count(load('outbox')) ?></a>
</div>
<?php if ($tab === 'email'): $box = load('outbox'); ?>
  <p class="t-small t-muted"><?= empty(config()['mail_enabled']) ? 'Chưa bật gửi thư thật (mail_enabled = false): thư chỉ được lưu ở đây.' : 'Đã bật gửi thư thật. Cột "Đã gửi" cho biết máy chủ có nhận gửi hay không.' ?> Thư không bao giờ chứa nội dung phiếu.</p>
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th scope="col" style="width:140px">Thời gian</th><th scope="col" style="width:200px">Người nhận</th><th scope="col">Tiêu đề · Nội dung</th><th scope="col" style="width:90px">Đã gửi</th></tr></thead>
      <tbody>
      <?php foreach (array_slice($box, 0, 200) as $m):
          // Ngoài bản chạy thử: che mã bí mật trong liên kết (tránh quản trị viên dùng liên kết của người khác)
          $body = SITE['is_demo'] ? $m['body'] : preg_replace('/token=[0-9a-f]+/', 'token=••••', $m['body']); ?>
        <tr>
          <td><?= e(fmt_dt($m['time'])) ?></td>
          <td style="word-break:break-all"><?= e($m['to']) ?></td>
          <td><p class="t-strong"><?= e($m['subject']) ?></p><p class="t-small pre"><?= e($body) ?></p></td>
          <td><?= $m['sent'] ? '<span class="badge badge--ok">Có</span>' : '<span class="badge badge--muted">Chưa</span>' ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$box): ?><tr><td colspan="4" class="table-empty">Chưa có email.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
<?php admin_end(); exit; endif; ?>
<form method="get" class="card card--admin" style="padding:16px">
  <div class="filter-grid" style="grid-template-columns:repeat(auto-fit,minmax(170px,1fr))">
    <?= field_input('tu', 'Từ ngày', $f['from'], ['type' => 'date']) ?>
    <?= field_input('den', 'Đến ngày', $f['to'], ['type' => 'date']) ?>
    <?= field_select('hanh-dong', 'Hành động', ['' => 'Tất cả'] + array_combine($actions, $actions), $f['action']) ?>
    <?= field_select('nguoi', 'Người thực hiện', ['' => 'Tất cả'] + array_combine($actors, $actors), $f['actor']) ?>
    <?= field_input('phieu', 'Mã phiếu', get('phieu'), ['placeholder' => '#…']) ?>
  </div>
  <div class="row gap-10">
    <button type="submit" class="btn btn--sm"><?= icon('search', 16) ?> Lọc</button>
    <a href="<?= e(url('qt/nhat-ky.php')) ?>" class="btn btn--sm btn--ghost">Xóa lọc</a>
  </div>
</form>

<div class="table-wrap">
  <table class="table">
    <thead><tr><th scope="col" style="width:150px">Thời gian</th><th scope="col" style="width:160px">Người thực hiện</th><th scope="col" style="width:150px">Hành động</th><th scope="col" style="width:120px">Phiếu</th><th scope="col">Chi tiết</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= e(fmt_dt($r['time'])) ?></td>
        <td><?= e($r['actor']) ?></td>
        <td><span class="badge <?= in_array($r['action'], $sensitive, true) ? 'badge--danger' : 'badge--info' ?>"><?= e($r['action']) ?></span></td>
        <td><?= $r['ticket'] ? '<a href="' . e(url('qt/xu-ly-phieu.php', ['id' => $r['ticket']])) . '">#' . e($r['ticket']) . '</a>' : '—' ?></td>
        <td><?= e($r['detail']) ?></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="5" class="table-empty">Không có bản ghi phù hợp.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php admin_end();
