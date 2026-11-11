<?php
/* QT11 – Hỏi đáp có điều phối + câu trả lời mở "Vì sao em chọn" (giáo viên / quản trị viên)
 * Không hiện tên học sinh: giáo viên trả lời theo nội dung, không theo người hỏi. */
require __DIR__ . '/../includes/bootstrap.php';
$me = require_staff('learning');

$tab = get('tab') === 'vi-sao' ? 'vi-sao' : 'cau-hoi';
$filter = in_array(get('loc'), ['cho', 'xong'], true) ? get('loc') : 'cho';

if (is_post()) {
    $q = question_find(post('id'));
    if ($q) {
        $a = post('action');
        if ($a === 'answer') {
            $q['answer'] = post('answer');
            $q['answered_by'] = $me['id'];
            $q['answered_at'] = now_iso();
            if ($q['kind'] === 'question') $q['published'] = !empty($_POST['published']);
            question_save($q);
            audit_log('Hỏi đáp', null, ($q['kind'] === 'why' ? 'Phản hồi câu trả lời mở' : 'Trả lời câu hỏi') . ($q['published'] ? ' và công bố' : ''), $me);
            flash('Đã lưu câu trả lời.');
        }
        if ($a === 'toggle' && $q['kind'] === 'question' && $q['answer'] !== '') {
            $q['published'] = !$q['published'];
            question_save($q);
            flash($q['published'] ? 'Đã công bố cho học sinh xem (không kèm tên).' : 'Đã ẩn khỏi trang học sinh.');
        }
    }
    redirect('qt/hoi-dap.php', ['tab' => $tab, 'loc' => $filter]);
}

$topicName = array_column(topics_all(), 'name', 'id');
$sitTitle = [];
foreach (topics_all() as $t) foreach ($t['situations'] as $s) $sitTitle[$s['id']] = $s['title'];

$kind = $tab === 'vi-sao' ? 'why' : 'question';
$ofKind = array_filter(questions_all(), fn ($q) => $q['kind'] === $kind);
$waiting = count(array_filter($ofKind, fn ($q) => $q['answer'] === ''));
$rows = array_filter($ofKind, fn ($q) => ($filter === 'cho') === ($q['answer'] === ''));
$countKind = fn (string $k) => count(array_filter(questions_all(), fn ($q) => $q['kind'] === $k && $q['answer'] === ''));

admin_start('Hỏi đáp', 'Hỏi đáp & câu trả lời mở', 'Câu hỏi chỉ hiện cho cả lớp khi đã trả lời và bật công bố. Không hiện tên học sinh.');
?>
<div class="row wrap gap-8">
  <a href="<?= e(url('qt/hoi-dap.php')) ?>" class="chip<?= $tab === 'cau-hoi' ? ' chip--active' : '' ?>">Câu hỏi · chờ <?= $countKind('question') ?></a>
  <a href="<?= e(url('qt/hoi-dap.php', ['tab' => 'vi-sao'])) ?>" class="chip<?= $tab === 'vi-sao' ? ' chip--active' : '' ?>">"Vì sao em chọn" · chờ <?= $countKind('why') ?></a>
  <span class="grow"></span>
  <a href="<?= e(url('qt/hoi-dap.php', ['tab' => $tab, 'loc' => 'cho'])) ?>" class="chip<?= $filter === 'cho' ? ' chip--active' : '' ?>">Chưa trả lời · <?= $waiting ?></a>
  <a href="<?= e(url('qt/hoi-dap.php', ['tab' => $tab, 'loc' => 'xong'])) ?>" class="chip<?= $filter === 'xong' ? ' chip--active' : '' ?>">Đã trả lời · <?= count($ofKind) - $waiting ?></a>
</div>

<?php foreach ($rows as $q): ?>
  <div class="card card--admin" style="gap:10px">
    <p class="t-small t-muted">
      <?= e($topicName[$q['topic_id']] ?? 'Chủ đề đã xóa') ?>
      <?php if ($q['kind'] === 'why'): ?> · <?= e($sitTitle[$q['situation_id']] ?? '') ?> · Chọn <?= e($q['choice']) ?><?php endif; ?>
      · <?= e(fmt_dt($q['created_at'])) ?>
      <?php if ($q['kind'] === 'question' && $q['answer'] !== ''): ?> · <?= $q['published'] ? '<span class="badge badge--ok">Đã công bố</span>' : '<span class="badge badge--muted">Chỉ người hỏi thấy</span>' ?><?php endif; ?>
    </p>
    <p class="pre"><?= e($q['text']) ?></p>
    <form method="post" class="stack gap-8">
      <?= csrf_field() ?><input type="hidden" name="action" value="answer"><input type="hidden" name="id" value="<?= e($q['id']) ?>">
      <?= field_textarea('answer', $q['kind'] === 'why' ? 'Phản hồi cho học sinh' : 'Trả lời', $q['answer'], ['id' => 'a-' . $q['id'], 'rows' => 3, 'required' => true]) ?>
      <div class="row wrap gap-12">
        <?php if ($q['kind'] === 'question'): ?>
          <label class="row gap-8 t-small grow"><input type="checkbox" name="published" value="1" class="checkbox" <?= $q['published'] || $q['answer'] === '' ? 'checked' : '' ?>> Công bố cho học sinh khác (không kèm tên)</label>
        <?php else: ?>
          <span class="t-small t-muted grow">Chỉ học sinh này thấy phản hồi.</span>
        <?php endif; ?>
        <button type="submit" class="btn btn--sm">Lưu trả lời</button>
      </div>
    </form>
    <?php if ($q['kind'] === 'question' && $q['answer'] !== ''): ?>
      <div><?= post_button($q['published'] ? 'Ẩn khỏi trang học sinh' : 'Công bố', ['action' => 'toggle', 'id' => $q['id']], 'link') ?></div>
    <?php endif; ?>
  </div>
<?php endforeach; ?>
<?php if (!$rows): ?><p class="t-muted">Không có mục nào.</p><?php endif; ?>
<?php admin_end();
