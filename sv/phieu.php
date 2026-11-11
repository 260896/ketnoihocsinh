<?php
/* SV19 – Phiếu của tôi */
require __DIR__ . '/../includes/bootstrap.php';
$me = require_student();

$q = mb_strtolower(ltrim(get('q'), '#'));
$mine = array_filter(tickets_all(), fn ($t) => $t['owner_id'] === $me['id'] && ($q === '' || str_contains(mb_strtolower($t['code']), $q)));

$note = function (array $t): array {
    $last = end($t['messages']);
    return match ($t['status']) {
        'Cần bổ sung'   => ['Cán bộ cần thêm thông tin', 't-danger'],
        'Đã tiếp nhận'  => ['Gửi ' . fmt_dt($t['created_at'], 'd/m/Y'), 't-muted'],
        default         => ['Cập nhật ' . time_ago($last['time']), 't-muted'],
    };
};

mobile_start('Phiếu của tôi');
?>
<form method="get" class="row gap-10" style="padding:4px 4px 4px 14px;border-radius:12px;background:var(--c-bg)" role="search">
  <?= icon('search', 18, 'var(--c-muted)') ?>
  <label for="q" class="sr-only">Tìm theo mã phiếu</label>
  <input id="q" name="q" value="<?= e(get('q')) ?>" placeholder="Tìm theo mã phiếu" class="grow" style="border:0;background:transparent;font-size:15px;padding:10px 0;outline:none">
  <button type="submit" class="btn btn--sm btn--ghost">Tìm</button>
</form>

<?php if (!$mine): ?>
  <p class="t-muted t-center"><?= $q ? 'Không tìm thấy phiếu.' : 'Bạn chưa gửi phiếu nào.' ?></p>
<?php endif; ?>

<?php foreach ($mine as $t): [$n, $cls] = $note($t); ?>
  <a href="<?= e(url('sv/chi-tiet-phieu.php', ['id' => $t['code']])) ?>" class="list-card">
    <span class="row gap-8">
      <span class="t-title grow">#<?= e($t['code']) ?></span>
      <?php if ($t['has_update']): ?><span class="dot" role="img" aria-label="Có cập nhật mới"></span><?php endif; ?>
      <?= icon('chevronRight', 18, 'var(--c-muted)') ?>
    </span>
    <span class="t-small t-muted"><?= e($t['type']) ?> · <?= $t['anonymous'] ? 'Ẩn danh' : 'Có danh tính' ?></span>
    <span class="row gap-8 wrap"><?= status_badge($t['status']) ?><span class="t-small <?= $cls ?>"><?= e($n) ?></span></span>
  </a>
<?php endforeach; ?>
<?php mobile_end('Phiếu của tôi');
