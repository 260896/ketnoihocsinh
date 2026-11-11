<?php
/* QT06 – Soạn tình huống: THÊM / SỬA câu hỏi, các lựa chọn (đáp án) và giải thích.
 *   ?chu-de=<id>  → thêm mới vào chủ đề
 *   ?id=<id>      → sửa tình huống có sẵn
 * Không dùng JavaScript: "Thêm lựa chọn" / "Xóa lựa chọn" là nút gửi form,
 * trang được vẽ lại với dữ liệu đang nhập dở. */
require __DIR__ . '/../includes/bootstrap.php';
$me = require_staff('learning');

$topics = topics_all();
if (!$topics) {
    flash('Hãy tạo chủ đề trước khi thêm tình huống.', 'error');
    redirect('qt/hoc-lieu.php');
}

$editing = null;
$topicId = get('chu-de');
if (get('id') !== '') {
    $found = situation_find(get('id'));
    if (!$found) redirect('qt/hoc-lieu.php');
    [$topic, $editing] = $found;
    $topicId = $topic['id'];
}
if (!topic_find($topicId)) $topicId = $topics[0]['id'];

const MIN_CHOICES = 2;
const MAX_CHOICES = 6;

// Dữ liệu đang soạn: từ bản đã lưu, hoặc mẫu trống
$s = $editing ?? [
    'id' => '', 'title' => '', 'question' => '', 'image' => '', 'video' => '',
    'choices' => [['text' => '', 'explanation' => ''], ['text' => '', 'explanation' => ''], ['text' => '', 'explanation' => '']],
    'best' => 0, 'status' => 'Nháp', 'order' => count(topic_find($topicId)['situations']) + 1, 'allow_retry' => true,
];
$err = [];

if (is_post()) {
    // 1. Đọc lại toàn bộ dữ liệu form
    $choices = [];
    foreach ((array) ($_POST['choices'] ?? []) as $c) {
        $choices[] = ['text' => trim((string) ($c['text'] ?? '')), 'explanation' => trim((string) ($c['explanation'] ?? ''))];
    }
    $s = array_merge($s, [
        'title'       => post('title'),
        'question'    => post('question'),
        'video'       => post('video'),
        'image'       => preg_match('#^images/[a-f0-9]+\.(jpe?g|png|webp)$#', post('image_current')) ? post('image_current') : '',
        'choices'     => $choices,
        'best'        => (int) post('best', '0'),
        'order'       => max(1, (int) post('order', '1')),
        'status'      => post('status') === 'Đã công bố' ? 'Đã công bố' : 'Nháp',
        'allow_retry' => !empty($_POST['allow_retry']),
    ]);
    $topicId = topic_find(post('topic_id')) ? post('topic_id') : $topicId;

    // 2. Hình minh họa
    if (!empty($_POST['remove_image'])) $s['image'] = '';
    if ($f = files_list('image')[0] ?? null) {
        $r = save_upload($f, 'images', ['jpg', 'jpeg', 'png', 'webp'], (int) config()['max_file_mb']);
        if (is_string($r)) $err['image'] = $r; else $s['image'] = $r['path'];
    }

    // 3. Hành động
    $action = post('action');
    if ($action === 'add_choice' && count($s['choices']) < MAX_CHOICES) {
        $s['choices'][] = ['text' => '', 'explanation' => ''];
    } elseif (str_starts_with($action, 'remove:') && count($s['choices']) > MIN_CHOICES) {
        $i = (int) substr($action, 7);
        array_splice($s['choices'], $i, 1);
        if ($s['best'] === $i) $s['best'] = 0;
        elseif ($s['best'] > $i) $s['best']--;
    } elseif (in_array($action, ['draft', 'publish', 'save'], true)) {
        if ($action === 'publish') $s['status'] = 'Đã công bố';
        if ($action === 'draft') $s['status'] = 'Nháp';

        // Kiểm tra dữ liệu
        if ($s['title'] === '') $err['title'] = 'Nhập tiêu đề tình huống.';
        if ($s['status'] === 'Đã công bố') {
            if ($s['question'] === '') $err['question'] = 'Nhập câu hỏi cho học sinh.';
            if (count($s['choices']) < MIN_CHOICES) $err['choices'] = 'Cần ít nhất ' . MIN_CHOICES . ' lựa chọn.';
            foreach ($s['choices'] as $i => $c) {
                if ($c['text'] === '') $err["c{$i}_text"] = 'Nhập nội dung lựa chọn ' . letter($i) . '.';
                if ($c['explanation'] === '') $err["c{$i}_exp"] = 'Nhập giải thích cho lựa chọn ' . letter($i) . '.';
            }
        }
        if (!isset($s['choices'][$s['best']])) $err['choices'] = 'Chọn cách ứng xử phù hợp nhất.';
        if ($s['video'] !== '' && !filter_var($s['video'], FILTER_VALIDATE_URL)) $err['video'] = 'Liên kết video không hợp lệ.';

        if (!$err) {
            $isNew = $s['id'] === '';
            if ($isNew) $s['id'] = $topicId . '-' . substr(new_id(), 0, 8);
            $s['updated_at'] = now_iso();
            situation_upsert($topicId, $s);
            flash(($isNew ? 'Đã thêm' : 'Đã lưu') . ' tình huống' . ($s['status'] === 'Đã công bố' ? ' và công bố cho học sinh.' : ' (bản nháp – học sinh chưa thấy).'));
            if (post('after') === 'new') redirect('qt/soan-tinh-huong.php', ['chu-de' => $topicId]);
            if ($s['status'] === 'Đã công bố' && $action === 'publish') redirect('qt/hoc-lieu.php', ['chu-de' => $topicId]);
            redirect('qt/soan-tinh-huong.php', ['id' => $s['id']]);
        }
        flash('Chưa lưu được – kiểm tra các ô báo lỗi.', 'error');
    }
}

$topicName = topic_find($topicId)['name'];
$isNew = $s['id'] === '';
$actions = '<button type="submit" form="sit" name="action" value="draft" class="btn btn--outline">Lưu nháp</button>'
    . '<button type="submit" form="sit" name="action" value="publish" class="btn">Công bố</button>';

admin_start('Học liệu', $isNew ? 'Thêm tình huống' : 'Sửa tình huống', $topicName . ' › ' . ($isNew ? 'Tình huống mới' : $s['title']), $actions);
?>
<p><a href="<?= e(url('qt/hoc-lieu.php', ['chu-de' => $topicId])) ?>" class="link link--lg">← Về danh sách tình huống</a></p>

<form method="post" id="sit" enctype="multipart/form-data" class="a-cols" novalidate>
  <?= csrf_field() ?>
  <!-- Nút mặc định khi nhấn Enter trong ô nhập: lưu (không phải "Xóa lựa chọn A") -->
  <button type="submit" name="action" value="save" class="sr-only" tabindex="-1" aria-hidden="true">Lưu</button>
  <input type="hidden" name="image_current" value="<?= e($s['image']) ?>">

  <div class="a-col-main">
    <div class="card card--admin">
      <p class="t-h2">Nội dung tình huống</p>
      <?= field_input('title', 'Tiêu đề tình huống *', $s['title'], ['placeholder' => 'Ví dụ: Bị chặn đường đòi tiền', 'error' => $err['title'] ?? null]) ?>
      <?= field_textarea('question', 'Câu hỏi *', $s['question'], ['placeholder' => 'Mô tả tình huống và câu hỏi cho học sinh', 'error' => $err['question'] ?? null]) ?>
      <div class="row wrap gap-14 items-end">
        <div style="flex:1 1 240px" class="stack gap-8">
          <?php if ($s['image']): ?>
            <img src="<?= e(url('tep.php', ['k' => 'img', 'f' => $s['image']])) ?>" alt="Hình minh họa hiện tại" style="max-height:140px;border-radius:12px;object-fit:cover">
            <label class="row gap-8 t-small"><input type="checkbox" name="remove_image" value="1" class="checkbox" style="width:18px;height:18px"> Xóa hình hiện tại</label>
          <?php endif; ?>
          <?= dropzone('image', $s['image'] ? 'Thay hình minh họa' : 'Tải hình minh họa (tùy chọn)', 'JPG, PNG, WEBP', ['accept' => 'image/*', 'icon' => 20]) ?>
          <?= field_error($err['image'] ?? null, 'image') ?>
        </div>
        <div style="flex:1 1 240px">
          <?= field_input('video', 'Liên kết video (tùy chọn)', $s['video'], ['type' => 'url', 'placeholder' => 'https://…', 'error' => $err['video'] ?? null]) ?>
        </div>
      </div>
    </div>

    <div class="card card--admin">
      <div>
        <p class="t-h2">Lựa chọn &amp; giải thích</p>
        <p class="t-small t-muted">Mỗi lựa chọn có phần giải thích riêng hiển thị sau khi học sinh chọn. Đánh dấu một lựa chọn là “Cách ứng xử phù hợp nhất”.</p>
      </div>
      <?= field_error($err['choices'] ?? null, 'choices') ?>

      <?php foreach ($s['choices'] as $i => $c): $L = letter($i); ?>
        <fieldset class="choice-editor" style="margin:0">
          <legend class="sr-only">Lựa chọn <?= $L ?></legend>
          <div class="row gap-10">
            <p class="t-title grow">Lựa chọn <?= $L ?></p>
            <label class="best-label">
              <input type="radio" name="best" value="<?= $i ?>" class="best-radio" <?= (int) $s['best'] === $i ? 'checked' : '' ?>>
              Cách ứng xử phù hợp nhất
            </label>
            <button type="submit" name="action" value="remove:<?= $i ?>" class="icon-btn" aria-label="Xóa lựa chọn <?= $L ?>" <?= count($s['choices']) <= MIN_CHOICES ? 'disabled' : '' ?> formnovalidate>
              <?= icon('x', 18, 'var(--c-muted)') ?>
            </button>
          </div>
          <?= field_input("choices[$i][text]", 'Nội dung lựa chọn *', $c['text'], ['id' => "c{$i}-text", 'placeholder' => "Nhập lựa chọn $L", 'error' => $err["c{$i}_text"] ?? null]) ?>
          <?= field_textarea("choices[$i][explanation]", 'Giải thích *', $c['explanation'], ['id' => "c{$i}-exp", 'rows' => 3, 'placeholder' => 'Vì sao phù hợp / chưa phù hợp, nên làm gì', 'error' => $err["c{$i}_exp"] ?? null]) ?>
        </fieldset>
      <?php endforeach; ?>

      <?php if (count($s['choices']) < MAX_CHOICES): ?>
        <button type="submit" name="action" value="add_choice" class="add-dashed" formnovalidate><?= icon('plus', 18, 'var(--c-primary)') ?> Thêm lựa chọn</button>
      <?php else: ?>
        <p class="t-small t-muted t-center">Tối đa <?= MAX_CHOICES ?> lựa chọn.</p>
      <?php endif; ?>
    </div>
  </div>

  <div class="a-col-side" style="flex-basis:340px">
    <div class="card card--admin">
      <p class="t-h2">Thiết lập</p>
      <?= field_select('topic_id', 'Chủ đề *', array_column($topics, 'name', 'id'), $topicId) ?>
      <?= field_input('order', 'Thứ tự hiển thị', (string) $s['order'], ['type' => 'number', 'min' => 1]) ?>
      <?= field_select('status', 'Trạng thái', ['Nháp', 'Đã công bố'], $s['status']) ?>
      <?= switch_row('allow_retry', 'Cho phép làm lại', 'Học sinh làm lại và xem lại giải thích', (bool) $s['allow_retry']) ?>
      <button type="submit" name="action" value="save" class="btn btn--block">Lưu theo trạng thái đã chọn</button>
      <label class="row gap-8 t-small" style="cursor:pointer">
        <input type="checkbox" name="after" value="new" class="checkbox" style="width:18px;height:18px">
        Lưu xong thì soạn tiếp tình huống mới
      </label>
    </div>
    <div class="panel panel--info" style="border-radius:14px;padding:20px;gap:8px">
      <div class="row gap-8"><?= icon('info', 18, 'var(--c-primary)') ?><p class="t-title t-primary">Xem trước</p></div>
      <p class="t-small">Bản nháp chỉ cán bộ thấy. Bấm Công bố để học sinh thấy tình huống này.</p>
      <?php if (!$isNew): ?>
        <a href="<?= e(url('sv/tinh-huong.php', ['xem-truoc' => $s['id']])) ?>" target="_blank" class="btn btn--outline btn--block">Xem trước trên điện thoại</a>
        <p class="t-small t-muted">Xem trước dùng bản đã lưu gần nhất.</p>
      <?php else: ?>
        <p class="t-small t-muted">Lưu nháp một lần để xem trước.</p>
      <?php endif; ?>
    </div>
  </div>
</form>
<?php admin_end();
