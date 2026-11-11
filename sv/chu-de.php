<?php
/* SV08 – Chủ đề */
require __DIR__ . '/../includes/bootstrap.php';
$me = require_student();

$topic = topic_find(get('id'));
if (!$topic) redirect('sv/hoc.php');

$list = published_situations($topic);
$doneIds = $me['progress'][$topic['id']] ?? [];
$done = count(array_intersect(array_column($list, 'id'), $doneIds));
$nextIdx = null;
foreach ($list as $i => $s) {
    if (!in_array($s['id'], $doneIds, true)) { $nextIdx = $i; break; }
}
$docs = array_filter(docs_all(), fn ($d) => $d['topic_id'] === $topic['id'] && $d['status'] === 'Đã công bố');

mobile_start($topic['name'], ['back' => url('sv/hoc.php')]);
?>
<div class="panel" style="background:var(--c-primary);border-radius:16px;padding:18px">
  <p style="font-size:22px;font-weight:700;color:#fff"><?= e($topic['name']) ?></p>
  <p class="t-small" style="color:#DCE6F5"><?= e($topic['description']) ?></p>
  <div style="height:6px;background:var(--c-primary-dark);border-radius:3px;overflow:hidden">
    <div style="width:<?= $list ? round($done / count($list) * 100) : 0 ?>%;height:6px;background:#fff;border-radius:3px"></div>
  </div>
  <p class="t-small t-strong" style="color:#fff">Đã làm <?= $done ?>/<?= count($list) ?> tình huống</p>
</div>

<?php if (trim($topic['content'] ?? '') !== ''): ?>
  <details class="card disclosure"<?= $done === 0 ? ' open' : '' ?>>
    <summary class="row gap-10">
      <?= icon('book', 20, 'var(--c-primary)') ?><span class="t-h2 grow">Nội dung học tập</span>
      <span class="when-closed"><?= icon('chevronDown', 18, 'var(--c-muted)') ?></span><span class="when-open"><?= icon('chevronUp', 18, 'var(--c-muted)') ?></span>
    </summary>
    <div class="stack gap-10" style="margin-top:10px">
      <?php foreach (preg_split('/\R\s*\R/', trim($topic['content'])) as $para): ?>
        <p class="pre" style="font-size:14px"><?= e($para) ?></p>
      <?php endforeach; ?>
    </div>
  </details>
<?php endif; ?>

<h2 class="t-h2">Tình huống</h2>
<?php foreach ($list as $i => $s):
    $isDone = in_array($s['id'], $doneIds, true);
    $isNext = $i === $nextIdx; ?>
  <a href="<?= e(url('sv/tinh-huong.php', ['chu-de' => $topic['id'], 'so' => $i + 1])) ?>" class="list-card list-card--row<?= $isNext ? ' list-card--current' : '' ?>">
    <span class="icon-circle" style="width:32px;height:32px;background:<?= $isDone ? 'var(--c-primary)' : ($isNext ? 'var(--c-primary-50)' : 'var(--c-neutral)') ?>">
      <?= $isDone ? icon('check', 16, '#fff') : '<span class="t-small t-strong" style="color:' . ($isNext ? 'var(--c-primary)' : 'var(--c-muted)') . '">' . ($i + 1) . '</span>' ?>
    </span>
    <span class="grow">
      <span class="t-title d-block"><?= e($s['title']) ?></span>
      <span class="t-small <?= $isNext ? 't-primary' : 't-muted' ?>"><?= $isDone ? 'Đã làm' . ($s['allow_retry'] ? ' · Làm lại được' : '') : ($isNext ? 'Làm tiếp' : 'Chưa làm') ?></span>
    </span>
    <?= icon('chevronRight', 18, 'var(--c-muted)') ?>
  </a>
<?php endforeach; ?>

<?php if ($docs): ?>
  <h2 class="t-h2">Tài liệu</h2>
  <?php foreach ($docs as $d):
      $href = $d['link'] ?: ($d['path'] ? url('tep.php', ['k' => 'doc', 'id' => $d['id']]) : '#'); ?>
    <a href="<?= e($href) ?>" class="list-card list-card--row" target="_blank" rel="noopener">
      <?= icon($d['format'] === 'Liên kết video' ? 'play' : 'file', 20, 'var(--c-primary)') ?>
      <span class="grow"><span class="t-title d-block"><?= e($d['name']) ?></span><span class="t-small t-muted"><?= e($d['format']) ?></span></span>
      <?= icon('chevronRight', 18, 'var(--c-muted)') ?>
    </a>
  <?php endforeach; ?>
<?php endif; ?>
<?php mobile_end('Học');
