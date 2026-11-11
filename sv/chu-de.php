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

// Hỏi đáp có điều phối: câu hỏi chỉ hiện cho cả lớp khi giáo viên đã trả lời và công bố (không kèm tên người hỏi)
$askErr = null;
if (is_post() && post('action') === 'ask') {
    $text = post('question');
    if (mb_strlen($text) < 5) {
        $askErr = 'Câu hỏi quá ngắn.';
    } elseif (mb_strlen($text) > 1000) {
        $askErr = 'Câu hỏi tối đa 1000 ký tự.';
    } else {
        question_save([
            'id' => new_id('q'), 'kind' => 'question', 'topic_id' => $topic['id'], 'situation_id' => null,
            'user_id' => $me['id'], 'text' => $text, 'choice' => null, 'created_at' => now_iso(),
            'answer' => '', 'answered_by' => null, 'answered_at' => null, 'published' => false,
        ]);
        flash('Đã gửi câu hỏi. Giáo viên sẽ trả lời trong mục Hỏi đáp.');
        redirect('sv/chu-de.php', ['id' => $topic['id']]);
    }
}
$qa = array_filter(questions_all(), fn ($q) => $q['kind'] === 'question' && $q['topic_id'] === $topic['id']
    && (($q['published'] && $q['answer'] !== '') || $q['user_id'] === $me['id']));

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

<?php
$media = fn (string $fmt) => array_values(array_filter($docs, fn ($d) => $d['format'] === $fmt && $d['path']))[0] ?? null;
$video = $media('Video');
$infographic = $media('Hình ảnh');
?>
<?php if ($infographic): ?>
  <details class="card disclosure"<?= $done === 0 ? ' open' : '' ?>>
    <summary class="row gap-10"><?= icon('file', 20, 'var(--c-primary)') ?><span class="t-h2 grow">Infographic</span><span class="when-closed"><?= icon('chevronDown', 18, 'var(--c-muted)') ?></span><span class="when-open"><?= icon('chevronUp', 18, 'var(--c-muted)') ?></span></summary>
    <a href="<?= e(url('tep.php', ['k' => 'doc', 'id' => $infographic['id']])) ?>" target="_blank" rel="noopener" style="margin-top:10px;display:block"><img src="<?= e(url('tep.php', ['k' => 'doc', 'id' => $infographic['id']])) ?>" alt="Infographic chủ đề <?= e($topic['name']) ?>" loading="lazy" style="width:100%;height:auto;border-radius:12px"></a>
  </details>
<?php endif; ?>
<?php if ($video): ?>
  <div class="card" style="gap:8px">
    <p class="t-h2">Video bài học</p>
    <video controls preload="none" playsinline style="width:100%;border-radius:12px;background:#000" aria-label="Video chủ đề <?= e($topic['name']) ?>"><source src="<?= e(url('tep.php', ['k' => 'doc', 'id' => $video['id']])) ?>" type="video/mp4"></video>
  </div>
<?php endif; ?>

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
      <?= icon(in_array($d['format'], ['Liên kết video', 'Video'], true) ? 'play' : 'file', 20, 'var(--c-primary)') ?>
      <span class="grow"><span class="t-title d-block"><?= e($d['name']) ?></span><span class="t-small t-muted"><?= e($d['format']) ?></span></span>
      <?= icon('chevronRight', 18, 'var(--c-muted)') ?>
    </a>
  <?php endforeach; ?>
<?php endif; ?>

<h2 class="t-h2" id="hoi-dap">Hỏi đáp</h2>
<?php foreach ($qa as $q): ?>
  <div class="card" style="gap:6px">
    <p class="t-small t-muted"><?= $q['user_id'] === $me['id'] ? 'Câu hỏi của bạn' : 'Một bạn hỏi' ?> · <?= e(fmt_dt($q['created_at'], 'd/m/Y')) ?></p>
    <p class="pre"><?= e($q['text']) ?></p>
    <?php if ($q['answer'] !== ''): ?>
      <div class="panel panel--info" style="padding:10px 12px;border-radius:12px"><p class="t-small t-strong t-primary">Giáo viên trả lời</p><p class="pre t-small"><?= e($q['answer']) ?></p></div>
    <?php else: ?>
      <p class="t-small t-muted">Đang chờ giáo viên trả lời.</p>
    <?php endif; ?>
  </div>
<?php endforeach; ?>
<?php if (!$qa): ?><p class="t-small t-muted">Chưa có câu hỏi nào được trả lời.</p><?php endif; ?>
<form method="post" class="stack gap-10">
  <?= csrf_field() ?><input type="hidden" name="action" value="ask">
  <?= field_textarea('question', 'Đặt câu hỏi cho thầy cô về chủ đề này', post('question'), ['rows' => 3, 'maxlength' => 1000, 'placeholder' => 'Không ghi tên thật của bạn bè. Câu hỏi được giáo viên đọc trước khi công bố.', 'error' => $askErr]) ?>
  <button type="submit" class="btn btn--outline btn--sm self-start">Gửi câu hỏi</button>
</form>
<?php mobile_end('Học');
