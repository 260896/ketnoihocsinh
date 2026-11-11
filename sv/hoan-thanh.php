<?php
/* SV10 – Hoàn thành chủ đề */
require __DIR__ . '/../includes/bootstrap.php';
$me = require_student();

$topic = topic_find(get('chu-de'));
if (!$topic) redirect('sv/hoc.php');
$list = published_situations($topic);

if (is_post()) {
    // Làm lại cả chủ đề: xóa tiến độ và các câu đã chọn
    $me['progress'][$topic['id']] = [];
    user_save($me);
    foreach ($list as $s) unset($_SESSION['quiz_' . $s['id']]);
    redirect('sv/tinh-huong.php', ['chu-de' => $topic['id'], 'so' => 1]);
}

$total = count($list);
$done = count(array_intersect(array_column($list, 'id'), $me['progress'][$topic['id']] ?? []));
$badges = learning_badges($me);
[, $correct, , $passed, $need] = learning_result($me, $topic);

mobile_start($topic['name'], ['back' => url('sv/chu-de.php', ['id' => $topic['id']])]);
?>
<div class="stack gap-14 items-center" style="padding-top:32px">
  <span class="icon-circle" style="width:88px;height:88px;background:var(--c-primary-50)"><?= icon('award', 44, 'var(--c-primary)') ?></span>
  <h2 class="t-h1 t-center"><?= $passed ? 'Bạn đã đạt chủ đề này' : ($done >= $total ? 'Bạn đã làm hết chủ đề' : 'Bạn đã đi hết chủ đề') ?></h2>
  <p class="t-muted t-center"><?= $done ?>/<?= $total ?> tình huống · Tiến độ đã được lưu vào tài khoản</p>
  <div class="card self-stretch">
    <div class="row gap-12"><p class="t-muted grow">Tình huống đã làm</p><p class="t-title"><?= $done ?>/<?= $total ?></p></div>
    <div class="row gap-12"><p class="t-muted grow">Trả lời phù hợp nhất</p><p class="t-title"><?= $correct ?>/<?= $total ?> · <?= $passed ? '<span class="badge badge--ok">Đạt</span>' : '<span class="badge badge--muted">Chưa đạt (cần ' . $need . ')</span>' ?></p></div>
    <div class="row gap-12"><p class="t-muted grow">Có thể làm lại</p><p class="t-title">Bất cứ lúc nào</p></div>
    <div class="row gap-12"><p class="t-muted grow">Huy hiệu học tập</p><p class="t-title"><?= count($badges) ?></p></div>
  </div>
  <?php if ($passed): ?><p class="badge badge--ok">Nhận huy hiệu: Hoàn thành <?= e($topic['name']) ?></p><?php elseif ($done >= $total): ?><p class="t-small t-muted t-center">Em có thể làm lại và xem giải thích. Kết quả chỉ để tự ôn tập, không dùng để đánh giá em.</p><?php endif; ?>
  <a href="<?= e(url('sv/hoc.php')) ?>" class="btn btn--block">Học chủ đề khác</a>
  <form method="post" class="self-stretch">
    <?= csrf_field() ?>
    <button type="submit" class="btn btn--outline btn--block">Làm lại chủ đề này</button>
  </form>
</div>
<?php mobile_end('Học');
