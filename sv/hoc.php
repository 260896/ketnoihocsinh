<?php
/* SV07 – Trang chủ (Học) */
require __DIR__ . '/../includes/bootstrap.php';
$me = require_student();
$topics = array_values(array_filter(topics_all(), fn ($t) => published_situations($t)));

mobile_start('Học', ['header' => 'Xin chào, ' . $me['name']]);
?>
<?= emergency_card() ?>
<div class="row gap-12">
  <h2 class="t-h2 grow">Chủ đề học</h2>
  <p class="t-small t-strong t-primary"><?= count($topics) ?> chủ đề</p>
</div>

<?php foreach ($topics as $t):
    $list = published_situations($t);
    $total = count($list);
    $done = count(array_intersect(array_column($list, 'id'), $me['progress'][$t['id']] ?? []));
    $label = $done === 0 ? 'Chưa bắt đầu' : ($done >= $total ? "$total/$total · Hoàn thành" : "$done/$total tình huống");
?>
  <a href="<?= e(url('sv/chu-de.php', ['id' => $t['id']])) ?>" class="list-card" style="gap:12px">
    <span class="row gap-12">
      <span class="icon-square" style="background:var(--c-primary-50)"><?= icon($t['icon'], 22, 'var(--c-primary)') ?></span>
      <span class="stack gap-2 grow"><span class="t-title"><?= e($t['name']) ?></span><span class="t-small t-muted"><?= e($label) ?></span></span>
      <?= icon('chevronRight', 20, 'var(--c-muted)') ?>
    </span>
    <span class="progress" aria-hidden="true"><span style="width:<?= $total ? round($done / $total * 100) : 0 ?>%"></span></span>
  </a>
<?php endforeach; ?>

<a href="<?= e(url('sv/tai-lieu.php')) ?>" class="list-card list-card--row"><?= icon('search', 20, 'var(--c-primary)') ?><span class="grow"><span class="t-title d-block">Tài liệu &amp; tìm kiếm</span><span class="t-small t-muted">Tìm học liệu, xem tài liệu đã lưu</span></span><?= icon('chevronRight', 18, 'var(--c-muted)') ?></a>

<?php if (!$topics): ?>
  <p class="t-muted t-center">Chưa có chủ đề nào được công bố.</p>
<?php endif; ?>

<a href="<?= e(url('sv/gui-phieu.php')) ?>" class="row gap-12" style="text-decoration:none;padding:16px;border-radius:16px;background:var(--c-danger-50);color:var(--c-text)">
  <span class="icon-square" style="background:var(--c-danger)"><?= icon('send', 22, '#fff') ?></span>
  <span class="grow"><span class="t-title t-danger d-block">Gửi phản ánh, đề nghị hỗ trợ</span><span class="t-small">Có thể gửi ẩn danh</span></span>
  <?= icon('chevronRight', 20, 'var(--c-danger)') ?>
</a>
<?php mobile_end('Học');
