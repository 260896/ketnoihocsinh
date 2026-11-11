<?php
/* SV09 – Tình huống & giải thích
 * ?chu-de=<id>&so=<thứ tự>  (cán bộ: thêm &xem-truoc=<id tình huống> để xem bản nháp) */
require __DIR__ . '/../includes/bootstrap.php';

$me = current_user();
$preview = null;

// Cán bộ xem trước một tình huống (kể cả bản nháp)
if (can_manage_learning($me) && get('xem-truoc') !== '') {
    $found = situation_find(get('xem-truoc'));
    if (!$found) redirect('qt/hoc-lieu.php');
    [$topic, $s] = $found;
    $list = [$s];
    $i = 0;
    $preview = true;
} else {
    $me = require_student();
    $topic = topic_find(get('chu-de'));
    if (!$topic) redirect('sv/hoc.php');
    $list = published_situations($topic);
    $i = (int) get('so', '1') - 1;
    $s = $list[$i] ?? null;
    if (!$s) redirect('sv/chu-de.php', ['id' => $topic['id']]);
}

$key = 'quiz_' . $s['id'];

if (is_post()) {
    if (isset($_POST['choice'])) {
        $pick = (int) $_POST['choice'];
        if (isset($s['choices'][$pick])) {
            $_SESSION[$key] = $pick;
            if (!$preview) {
                $done = $me['progress'][$topic['id']] ?? [];
                if (!in_array($s['id'], $done, true)) $me['progress'][$topic['id']] = [...$done, $s['id']];
                // Ghi đúng/sai của lần chọn gần nhất để tính đạt (≥ 2/3) – không dùng để kết luận học sinh vi phạm
                $me['answers'][$s['id']] = $pick === (int) $s['best'];
                user_save($me);
            }
        }
    } elseif (post('action') === 'why' && !$preview && isset($_SESSION[$key]) && post('why') !== '') {
        // Câu trả lời mở "Vì sao em chọn" – chỉ giáo viên và chính học sinh xem
        question_save([
            'id' => new_id('w'), 'kind' => 'why', 'topic_id' => $topic['id'], 'situation_id' => $s['id'],
            'user_id' => $me['id'], 'text' => mb_substr(post('why'), 0, 1000), 'choice' => letter((int) $_SESSION[$key]),
            'created_at' => now_iso(), 'answer' => '', 'answered_by' => null, 'answered_at' => null, 'published' => false,
        ]);
        flash('Đã gửi cho thầy cô. Cảm ơn em đã chia sẻ.');
    } elseif (post('action') === 'reset' && ($s['allow_retry'] || $preview)) {
        unset($_SESSION[$key]);
    }
    redirect_back();
}

$picked = $_SESSION[$key] ?? null;
$total = count($list);
$nextUrl = $preview ? null : ($i + 1 < $total
    ? url('sv/tinh-huong.php', ['chu-de' => $topic['id'], 'so' => $i + 2])
    : url('sv/hoan-thanh.php', ['chu-de' => $topic['id']]));
$back = $preview ? url('qt/soan-tinh-huong.php', ['id' => $s['id']]) : url('sv/chu-de.php', ['id' => $topic['id']]);

mobile_start($preview ? 'Xem trước' : 'Tình huống ' . ($i + 1) . '/' . $total, ['back' => $back]);
?>
<?php if ($preview): ?>
  <div class="demo-box">Bản xem trước của cán bộ · Trạng thái: <strong><?= e($s['status']) ?></strong>. Lựa chọn không được lưu vào tiến độ.</div>
<?php endif; ?>
<div class="progress" aria-hidden="true"><span style="width:<?= round(($i + 1) / max(1, $total) * 100) ?>%"></span></div>

<?php if ($s['image'] || $s['video']): ?>
  <div class="media-box">
    <?php if ($s['image']): ?>
      <img src="<?= e(url('tep.php', ['k' => 'img', 'f' => $s['image']])) ?>" alt="Hình minh họa tình huống">
    <?php else: ?>
      <a href="<?= e($s['video']) ?>" target="_blank" rel="noopener" class="stack gap-8 items-center" style="text-decoration:none;color:var(--c-muted)">
        <?= icon('play', 40, 'var(--c-muted)') ?><span class="t-small">Xem video minh họa</span>
      </a>
    <?php endif; ?>
  </div>
<?php endif; ?>

<p class="t-h2" style="line-height:1.5"><?= nl2br(e($s['question'])) ?></p>

<form method="post" class="stack gap-16">
  <?= csrf_field() ?>
  <?php foreach ($s['choices'] as $ci => $c): $on = $picked === $ci; ?>
    <button type="submit" name="choice" value="<?= $ci ?>" class="choice<?= $on ? ' is-selected' : '' ?>" aria-pressed="<?= $on ? 'true' : 'false' ?>">
      <span class="choice__radio"></span>
      <span class="stack gap-2">
        <span class="t-title"><?= letter($ci) ?>. <?= e($c['text']) ?></span>
        <?php if ($on): ?><span class="t-small t-muted">Bạn đã chọn</span><?php endif; ?>
      </span>
    </button>
  <?php endforeach; ?>
</form>

<?php if ($picked !== null && isset($s['choices'][$picked])):
    $others = array_filter($s['choices'], fn ($k) => $k !== $picked, ARRAY_FILTER_USE_KEY);
    $isBest = $picked === (int) $s['best']; ?>
  <div class="panel panel--info" style="border-radius:16px;padding:16px" aria-live="polite">
    <div class="row gap-8">
      <?= icon('info', 18, 'var(--c-primary)') ?>
      <p class="t-title t-primary">Giải thích cho lựa chọn <?= letter($picked) ?></p>
      <?php if ($isBest): ?><span class="badge badge--ok">Phù hợp nhất</span><?php endif; ?>
    </div>
    <p class="pre"><?= e($s['choices'][$picked]['explanation']) ?></p>
    <?php if ($others): ?>
      <details class="disclosure">
        <summary class="link link--lg">
          <span class="when-closed">Xem giải thích lựa chọn <?= e(implode(', ', array_map('letter', array_keys($others)))) ?></span>
          <span class="when-open">Ẩn giải thích các lựa chọn khác</span>
        </summary>
        <div class="stack gap-8" style="margin-top:10px">
          <?php foreach ($others as $k => $c): ?>
            <div class="stack gap-4" style="border-top:1px solid var(--c-primary-100);padding-top:8px">
              <p class="t-small t-strong">Lựa chọn <?= letter($k) ?><?= $k === (int) $s['best'] ? ' · Phù hợp nhất' : '' ?></p>
              <p class="t-small pre"><?= e($c['explanation']) ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      </details>
    <?php endif; ?>
  </div>

  <?php if (!$preview): $why = why_answer_of($me['id'], $s['id']); ?>
    <div class="card" style="gap:8px">
      <?php if ($why): ?>
        <p class="t-small t-muted">Em đã chia sẻ (chọn <?= e($why['choice']) ?>):</p>
        <p class="pre t-small"><?= e($why['text']) ?></p>
        <?php if ($why['answer'] !== ''): ?>
          <div class="panel panel--info" style="padding:10px 12px;border-radius:12px"><p class="t-small t-strong t-primary">Thầy cô phản hồi</p><p class="pre t-small"><?= e($why['answer']) ?></p></div>
        <?php else: ?>
          <p class="t-small t-muted">Thầy cô sẽ đọc và phản hồi.</p>
        <?php endif; ?>
      <?php else: ?>
        <form method="post" class="stack gap-8">
          <?= csrf_field() ?><input type="hidden" name="action" value="why">
          <?= field_textarea('why', 'Vì sao em chọn như vậy? (không bắt buộc)', '', ['rows' => 2, 'maxlength' => 1000, 'placeholder' => 'Chỉ thầy cô phụ trách học liệu đọc được.']) ?>
          <button type="submit" class="btn btn--outline btn--sm self-start">Gửi cho thầy cô</button>
        </form>
      <?php endif; ?>
    </div>
  <?php endif; ?>
<?php endif; ?>

<?php mobile_footer(); ?>
  <form method="post" style="display:flex">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="reset">
    <button type="submit" class="btn btn--outline btn--block" <?= ($picked === null || (!$s['allow_retry'] && !$preview)) ? 'disabled' : '' ?>>Làm lại</button>
  </form>
  <?php if ($preview): ?>
    <a href="<?= e($back) ?>" class="btn">Quay lại soạn</a>
  <?php else: ?>
    <a href="<?= e($nextUrl) ?>" class="btn<?= $picked === null ? ' is-disabled' : '' ?>" <?= $picked === null ? 'aria-disabled="true" tabindex="-1"' : '' ?>>Tình huống tiếp</a>
  <?php endif; ?>
<?php mobile_end();
