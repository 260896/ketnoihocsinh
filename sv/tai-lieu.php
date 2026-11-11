<?php
/* SV21 – Tài liệu & tìm kiếm học liệu (?q=…). "Tài liệu đã lưu" thay cho giỏ hàng (CLAUDE.md mục 9). */
require __DIR__ . '/../includes/bootstrap.php';
$me = require_student();

$docs = array_values(array_filter(docs_all(), fn ($d) => $d['status'] === 'Đã công bố'));
$saved = $me['saved_docs'] ?? [];

if (is_post()) {
    $id = post('doc');
    if (in_array($id, array_column($docs, 'id'), true)) {
        $saved = in_array($id, $saved, true) ? array_values(array_diff($saved, [$id])) : [...$saved, $id];
        $me['saved_docs'] = $saved;
        user_save($me);
    }
    redirect_back();
}

$q = trim(get('q'));
$qq = mb_strtolower($q);
$has = fn (string ...$texts) => $qq === '' || str_contains(mb_strtolower(implode(' ', $texts)), $qq);

$topics = array_values(array_filter(topics_all(), fn ($t) => published_situations($t)));
$topicName = array_column($topics, 'name', 'id');
$hitTopics = array_filter($topics, fn ($t) => $has($t['name'], $t['description'], $t['content'] ?? ''));
$hitSits = [];
foreach ($topics as $t) {
    foreach (published_situations($t) as $i => $s) {
        if ($qq !== '' && $has($s['title'], $s['question'])) $hitSits[] = [$t, $s, $i];
    }
}
$hitDocs = array_filter($docs, fn ($d) => isset($topicName[$d['topic_id']]) && $has($d['name'], $d['format'], $topicName[$d['topic_id']]));
$savedDocs = array_filter($docs, fn ($d) => in_array($d['id'], $saved, true));

$docRow = function (array $d) use ($saved, $topicName): string {
    $href = $d['link'] ?: ($d['path'] ? url('tep.php', ['k' => 'doc', 'id' => $d['id']]) : '#');
    $on = in_array($d['id'], $saved, true);
    return '<div class="list-card list-card--row">'
        . icon($d['format'] === 'Liên kết video' ? 'play' : 'file', 20, 'var(--c-primary)')
        . '<a class="grow" style="text-decoration:none;color:inherit" href="' . e($href) . '" target="_blank" rel="noopener"><span class="t-title d-block">' . e($d['name']) . '</span>'
        . '<span class="t-small t-muted">' . e($d['format']) . ' · ' . e($topicName[$d['topic_id']] ?? '') . '</span></a>'
        . post_button($on ? 'Bỏ lưu' : 'Lưu', ['doc' => $d['id']], 'btn btn--sm ' . ($on ? 'btn--ghost' : 'btn--outline'))
        . '</div>';
};

mobile_start('Tài liệu', ['back' => url('sv/hoc.php')]);
?>
<form method="get" class="row gap-10" style="padding:4px 4px 4px 14px;border-radius:12px;background:var(--c-bg)" role="search">
  <?= icon('search', 18, 'var(--c-muted)') ?>
  <label for="q" class="sr-only">Tìm học liệu</label>
  <input id="q" name="q" value="<?= e($q) ?>" placeholder="Tìm chủ đề, tình huống, tài liệu" class="grow" style="border:0;background:transparent;font-size:15px;padding:10px 0;outline:none">
  <button type="submit" class="btn btn--sm btn--ghost">Tìm</button>
</form>

<?php if ($q === ''): ?>
  <h2 class="t-h2">Tài liệu đã lưu</h2>
  <?php foreach ($savedDocs as $d) echo $docRow($d); ?>
  <?php if (!$savedDocs): ?><p class="t-small t-muted">Bấm "Lưu" ở một tài liệu để xem lại nhanh tại đây.</p><?php endif; ?>
<?php else: ?>
  <p class="t-small t-muted">Kết quả cho “<?= e($q) ?>”</p>
<?php endif; ?>

<?php if ($hitTopics && $q !== ''): ?>
  <h2 class="t-h2">Chủ đề</h2>
  <?php foreach ($hitTopics as $t): ?>
    <a href="<?= e(url('sv/chu-de.php', ['id' => $t['id']])) ?>" class="list-card list-card--row"><?= icon($t['icon'], 20, 'var(--c-primary)') ?><span class="t-title grow"><?= e($t['name']) ?></span><?= icon('chevronRight', 18, 'var(--c-muted)') ?></a>
  <?php endforeach; ?>
<?php endif; ?>

<?php if ($hitSits): ?>
  <h2 class="t-h2">Tình huống</h2>
  <?php foreach ($hitSits as [$t, $s, $i]): ?>
    <a href="<?= e(url('sv/tinh-huong.php', ['chu-de' => $t['id'], 'so' => $i + 1])) ?>" class="list-card list-card--row"><span class="grow"><span class="t-title d-block"><?= e($s['title']) ?></span><span class="t-small t-muted"><?= e($t['name']) ?></span></span><?= icon('chevronRight', 18, 'var(--c-muted)') ?></a>
  <?php endforeach; ?>
<?php endif; ?>

<h2 class="t-h2"><?= $q === '' ? 'Tất cả tài liệu' : 'Tài liệu' ?></h2>
<?php if ($q === ''): ?><a href="<?= e(url('trang.php', ['p' => 'tham-khao'])) ?>" class="list-card list-card--row"><?= icon('book', 20, 'var(--c-primary)') ?><span class="grow"><span class="t-title d-block">Danh mục tài liệu tham khảo</span><span class="t-small t-muted">Văn bản pháp luật và bài nghiên cứu dùng để xây dựng 5 chủ đề</span></span><?= icon('chevronRight', 18, 'var(--c-muted)') ?></a><?php endif; ?>
<?php foreach ($hitDocs as $d) echo $docRow($d); ?>
<?php if (!$hitDocs): ?><p class="t-small t-muted"><?= $q === '' ? 'Chưa có tài liệu nào được công bố.' : 'Không có tài liệu phù hợp.' ?></p><?php endif; ?>
<?php if ($q !== '' && !$hitTopics && !$hitSits && !$hitDocs): ?><p class="t-muted t-center">Không tìm thấy kết quả.</p><?php endif; ?>
<?php mobile_end('Học');
