<?php
/* QT05 – Học liệu: quản lý CHỦ ĐỀ, danh sách TÌNH HUỐNG (câu hỏi) và TÀI LIỆU */
require __DIR__ . '/../includes/bootstrap.php';
$me = require_staff('learning');

$topics = topics_all();
$active = topic_find(get('chu-de')) ?? ($topics[0] ?? null);
$back = fn (?string $topicId = null) => redirect('qt/hoc-lieu.php', ['chu-de' => $topicId ?? $active['id'] ?? null]);

if (is_post()) {
    $a = post('action');
    $all = load('topics');

    // ---------- Chủ đề ----------
    if ($a === 'topic_add' || $a === 'topic_edit') {
        $name = post('name');
        if ($name === '') {
            flash('Tên chủ đề không được để trống.', 'error');
            $back();
        }
        $icon = array_key_exists(post('icon'), TOPIC_ICONS) ? post('icon') : 'book';
        if ($a === 'topic_add') {
            $id = slugify($name);
            if (topic_find($id)) $id .= '-' . substr(new_id(), 0, 4);
            $all[] = ['id' => $id, 'name' => $name, 'icon' => $icon, 'description' => post('description'), 'content' => post('content'), 'order' => count($all) + 1, 'situations' => []];
            topics_save($all);
            flash('Đã thêm chủ đề “' . $name . '”. Bây giờ hãy thêm tình huống.');
            $back($id);
        }
        foreach ($all as &$t) {
            if ($t['id'] === post('id')) {
                $t['name'] = $name;
                $t['icon'] = $icon;
                $t['description'] = post('description');
                $t['content'] = post('content');
                $t['order'] = max(1, (int) post('order', '1'));
            }
        }
        unset($t);
        topics_save($all);
        flash('Đã cập nhật chủ đề.');
        $back(post('id'));
    }
    if ($a === 'topic_delete') {
        topics_save(array_filter($all, fn ($t) => $t['id'] !== post('id')));
        flash('Đã xóa chủ đề và các tình huống trong đó.');
        redirect('qt/hoc-lieu.php');
    }

    // ---------- Tình huống ----------
    if ($a === 'sit_status') {
        $status = post('status') === 'Đã công bố' ? 'Đã công bố' : 'Nháp';
        situation_update(post('id'), fn ($s) => ['status' => $status, 'updated_at' => now_iso()] + $s);
        flash($status === 'Đã công bố' ? 'Đã công bố tình huống. Học sinh đã thấy.' : 'Đã gỡ công bố – học sinh không còn thấy.');
        $back();
    }
    if ($a === 'sit_delete') {
        situation_delete(post('id'));
        flash('Đã xóa tình huống.');
        $back();
    }
    if ($a === 'sit_move' && $active) {
        // Đổi chỗ với tình huống liền trước/sau rồi đánh số lại 1..n
        $list = $active['situations'];
        $idx = array_search(post('id'), array_column($list, 'id'), true);
        $to = $idx + (post('dir') === 'up' ? -1 : 1);
        if ($idx !== false && isset($list[$to])) {
            [$list[$idx], $list[$to]] = [$list[$to], $list[$idx]];
            $orders = [];
            foreach ($list as $n => $s) $orders[$s['id']] = $n + 1;
            foreach ($all as &$t) {
                if ($t['id'] !== $active['id']) continue;
                foreach ($t['situations'] as &$s) $s['order'] = $orders[$s['id']];
            }
            unset($t, $s);
            topics_save($all);
        }
        $back();
    }

    // ---------- Tài liệu ----------
    $docs = docs_all();
    if ($a === 'doc_add') {
        $name = post('name');
        $format = in_array(post('format'), ['PDF', 'Hình ảnh', 'Liên kết video'], true) ? post('format') : 'PDF';
        $link = post('link');
        $path = '';
        if ($f = files_list('file')[0] ?? null) {
            $r = save_upload($f, 'docs', ['pdf', 'jpg', 'jpeg', 'png'], (int) config()['max_file_mb']);
            if (is_string($r)) { flash($r, 'error'); $back(post('topic_id')); }
            $path = $r['path'];
            $name = $name ?: $r['name'];
        }
        if ($name === '' || ($path === '' && $link === '')) {
            flash('Nhập tên tài liệu và chọn tệp hoặc dán liên kết video.', 'error');
            $back(post('topic_id'));
        }
        if ($link !== '' && !filter_var($link, FILTER_VALIDATE_URL)) {
            flash('Liên kết video không hợp lệ.', 'error');
            $back(post('topic_id'));
        }
        $status = post('publish') === '1' ? 'Đã công bố' : 'Nháp';
        $docs[] = ['id' => new_id('d'), 'topic_id' => post('topic_id'), 'name' => $name, 'format' => $link !== '' && $path === '' ? 'Liên kết video' : $format, 'status' => $status, 'path' => $path, 'link' => $link];
        docs_save($docs);
        flash($status === 'Đã công bố' ? 'Đã công bố tài liệu.' : 'Đã lưu nháp tài liệu.');
        $back(post('topic_id'));
    }
    if ($a === 'doc_status' || $a === 'doc_delete') {
        if ($a === 'doc_delete') {
            $docs = array_filter($docs, fn ($d) => $d['id'] !== post('id'));
        } else {
            foreach ($docs as &$d) if ($d['id'] === post('id')) $d['status'] = post('status') === 'Đã công bố' ? 'Đã công bố' : 'Nháp';
            unset($d);
        }
        docs_save($docs);
        $back();
    }
    $back();
}

$docs = $active ? array_filter(docs_all(), fn ($d) => $d['topic_id'] === $active['id']) : [];
$actions = $active
    ? '<a href="#tai-lieu" class="btn btn--outline">Tải tài liệu</a><a href="' . e(url('qt/soan-tinh-huong.php', ['chu-de' => $active['id']])) . '" class="btn">' . icon('plus', 18) . ' Thêm tình huống</a>'
    : '';

admin_start('Học liệu', 'Học liệu', 'Chủ đề, tình huống và tài liệu. Học sinh chỉ thấy bản đã công bố.', $actions);
?>
<div class="a-cols">
  <div class="a-col-main">

    <!-- Chủ đề -->
    <div class="row wrap gap-8">
      <?php foreach ($topics as $t): ?>
        <a href="<?= e(url('qt/hoc-lieu.php', ['chu-de' => $t['id']])) ?>" class="chip<?= $active && $t['id'] === $active['id'] ? ' chip--active' : '' ?>"><?= e($t['name']) ?> · <?= count($t['situations']) ?></a>
      <?php endforeach; ?>
      <details class="disclosure" style="width:100%"<?= !$topics ? ' open' : '' ?>>
        <summary class="chip" style="display:inline-block">+ Chủ đề mới</summary>
        <form method="post" class="card card--admin" style="margin-top:10px">
          <?= csrf_field() ?><input type="hidden" name="action" value="topic_add">
          <p class="t-h2">Thêm chủ đề</p>
          <div class="filter-grid" style="grid-template-columns:repeat(auto-fit,minmax(220px,1fr))">
            <?= field_input('name', 'Tên chủ đề *', '', ['id' => 'new-topic-name', 'placeholder' => 'Ví dụ: An toàn giao thông', 'required' => true]) ?>
            <?= field_select('icon', 'Biểu tượng', TOPIC_ICONS, 'book', ['id' => 'new-topic-icon']) ?>
          </div>
          <?= field_textarea('description', 'Mô tả ngắn', '', ['id' => 'new-topic-desc', 'rows' => 2, 'placeholder' => 'Hiện ở danh sách chủ đề']) ?>
          <?= field_textarea('content', 'Nội dung học tập', '', ['id' => 'new-topic-content', 'rows' => 6, 'placeholder' => 'Kiến thức học sinh đọc trước khi làm tình huống. Xuống dòng hai lần để tách đoạn.']) ?>
          <div><button type="submit" class="btn">Thêm chủ đề</button></div>
        </form>
      </details>
    </div>

    <?php if (!$active): ?>
      <div class="card card--admin"><p class="t-muted">Chưa có chủ đề nào. Thêm chủ đề đầu tiên ở trên.</p></div>
    <?php else: ?>

    <!-- Thông tin chủ đề đang chọn -->
    <div class="card card--admin">
      <div class="row gap-12">
        <span class="icon-square" style="background:var(--c-primary-50)"><?= icon($active['icon'], 22, 'var(--c-primary)') ?></span>
        <div class="grow">
          <p class="t-h2"><?= e($active['name']) ?></p>
          <p class="t-small t-muted"><?= e($active['description'] ?: 'Chưa có mô tả') ?> · <?= count(published_situations($active)) ?>/<?= count($active['situations']) ?> tình huống đã công bố</p>
        </div>
      </div>
      <details class="disclosure">
        <summary class="link link--lg"><span class="when-closed">Sửa chủ đề</span><span class="when-open">Đóng</span></summary>
        <form method="post" class="stack gap-14" style="margin-top:12px">
          <?= csrf_field() ?><input type="hidden" name="action" value="topic_edit"><input type="hidden" name="id" value="<?= e($active['id']) ?>">
          <div class="filter-grid" style="grid-template-columns:repeat(auto-fit,minmax(200px,1fr))">
            <?= field_input('name', 'Tên chủ đề *', $active['name'], ['required' => true]) ?>
            <?= field_select('icon', 'Biểu tượng', TOPIC_ICONS, $active['icon']) ?>
            <?= field_input('order', 'Thứ tự hiển thị', (string) $active['order'], ['type' => 'number', 'min' => 1]) ?>
          </div>
          <?= field_textarea('description', 'Mô tả ngắn', $active['description'], ['rows' => 2]) ?>
          <?= field_textarea('content', 'Nội dung học tập', $active['content'] ?? '', ['rows' => 8, 'placeholder' => 'Kiến thức học sinh đọc trước khi làm tình huống. Xuống dòng hai lần để tách đoạn.']) ?>
          <div class="row gap-10 wrap">
            <button type="submit" class="btn">Lưu chủ đề</button>
          </div>
        </form>
        <details class="disclosure" style="margin-top:12px">
          <summary class="link link--danger">Xóa chủ đề này…</summary>
          <div class="panel panel--danger" style="margin-top:8px">
            <p class="t-small">Xóa chủ đề sẽ xóa luôn <strong><?= count($active['situations']) ?> tình huống</strong> bên trong. Không hoàn tác được.</p>
            <div><?= post_button('Xác nhận xóa chủ đề', ['action' => 'topic_delete', 'id' => $active['id']], 'btn btn--danger btn--sm') ?></div>
          </div>
        </details>
      </details>
    </div>

    <!-- Danh sách tình huống -->
    <div class="table-wrap">
      <table class="table">
        <thead><tr>
          <th scope="col" style="width:70px">Thứ tự</th>
          <th scope="col">Tình huống (câu hỏi)</th>
          <th scope="col" style="width:100px">Lựa chọn</th>
          <th scope="col" style="width:120px">Trạng thái</th>
          <th scope="col" style="width:110px">Cập nhật</th>
          <th scope="col" style="width:250px"><span class="sr-only">Thao tác</span></th>
        </tr></thead>
        <tbody>
        <?php foreach ($active['situations'] as $n => $s): $last = $n === count($active['situations']) - 1; ?>
          <tr>
            <td>
              <div class="row gap-2">
                <?= $n > 0 ? post_button('↑', ['action' => 'sit_move', 'id' => $s['id'], 'dir' => 'up'], 'icon-btn') : '<span style="width:44px"></span>' ?>
                <?= !$last ? post_button('↓', ['action' => 'sit_move', 'id' => $s['id'], 'dir' => 'down'], 'icon-btn') : '' ?>
              </div>
            </td>
            <td>
              <a href="<?= e(url('qt/soan-tinh-huong.php', ['id' => $s['id']])) ?>" style="text-decoration:none"><?= e($s['title']) ?></a>
              <p class="t-small t-muted" style="font-weight:400"><?= e(mb_strimwidth($s['question'], 0, 90, '…')) ?></p>
            </td>
            <td><?= count($s['choices']) ?> · đúng: <?= letter((int) $s['best']) ?></td>
            <td><?= publish_badge($s['status']) ?></td>
            <td><?= e(fmt_dt($s['updated_at'], 'd/m/Y')) ?></td>
            <td>
              <div class="table-actions">
                <a href="<?= e(url('qt/soan-tinh-huong.php', ['id' => $s['id']])) ?>" class="link">Sửa</a>
                <a href="<?= e(url('sv/tinh-huong.php', ['xem-truoc' => $s['id']])) ?>" class="link" target="_blank">Xem trước</a>
                <?= $s['status'] === 'Nháp'
                    ? post_button('Công bố', ['action' => 'sit_status', 'id' => $s['id'], 'status' => 'Đã công bố'])
                    : post_button('Gỡ công bố', ['action' => 'sit_status', 'id' => $s['id'], 'status' => 'Nháp'], 'link link--danger') ?>
                <details class="disclosure">
                  <summary class="link link--danger">Xóa</summary>
                  <div style="margin-top:6px"><?= post_button('Chắc chắn xóa', ['action' => 'sit_delete', 'id' => $s['id']], 'btn btn--danger btn--sm') ?></div>
                </details>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$active['situations']): ?>
          <tr><td colspan="6" class="table-empty">Chủ đề chưa có tình huống. <a href="<?= e(url('qt/soan-tinh-huong.php', ['chu-de' => $active['id']])) ?>">Thêm tình huống đầu tiên</a>.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
    <div>
      <a href="<?= e(url('qt/soan-tinh-huong.php', ['chu-de' => $active['id']])) ?>" class="add-dashed" style="text-decoration:none"><?= icon('plus', 18, 'var(--c-primary)') ?> Thêm tình huống (câu hỏi &amp; đáp án) vào “<?= e($active['name']) ?>”</a>
    </div>

    <!-- Tài liệu -->
    <p class="t-h2">Tài liệu</p>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th scope="col">Tài liệu</th><th scope="col" style="width:130px">Định dạng</th><th scope="col" style="width:120px">Trạng thái</th><th scope="col" style="width:200px"><span class="sr-only">Thao tác</span></th></tr></thead>
        <tbody>
        <?php foreach ($docs as $d): ?>
          <tr>
            <td><?= e($d['name']) ?></td>
            <td><?= e($d['format']) ?></td>
            <td><?= publish_badge($d['status']) ?></td>
            <td><div class="table-actions">
              <?php if ($d['path'] || $d['link']): ?><a class="link" target="_blank" rel="noopener" href="<?= e($d['link'] ?: url('tep.php', ['k' => 'doc', 'id' => $d['id']])) ?>">Mở</a><?php endif; ?>
              <?= $d['status'] === 'Đã công bố'
                  ? post_button('Gỡ công bố', ['action' => 'doc_status', 'id' => $d['id'], 'status' => 'Nháp'], 'link link--danger')
                  : post_button('Công bố', ['action' => 'doc_status', 'id' => $d['id'], 'status' => 'Đã công bố']) ?>
              <?= post_button('Xóa', ['action' => 'doc_delete', 'id' => $d['id']], 'link link--danger') ?>
            </div></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$docs): ?><tr><td colspan="4" class="table-empty">Chưa có tài liệu.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

  <?php if ($active): ?>
  <div class="a-col-side" style="flex-basis:380px">
    <form method="post" enctype="multipart/form-data" class="card card--admin" id="tai-lieu">
      <?= csrf_field() ?><input type="hidden" name="action" value="doc_add">
      <p class="t-h2">Tải lên tài liệu</p>
      <?= field_input('name', 'Tên tài liệu *', '', ['placeholder' => 'Nhập tên hiển thị']) ?>
      <?= field_select('topic_id', 'Chủ đề *', array_column($topics, 'name', 'id'), $active['id']) ?>
      <?= field_select('format', 'Loại *', ['PDF', 'Hình ảnh', 'Liên kết video']) ?>
      <?= dropzone('file', 'Kéo thả hoặc chọn tệp', 'PDF, JPG, PNG · tối đa ' . config()['max_file_mb'] . ' MB', ['accept' => '.pdf,.jpg,.jpeg,.png']) ?>
      <?= field_input('link', 'Hoặc liên kết video', '', ['type' => 'url', 'placeholder' => 'https://…']) ?>
      <div class="row gap-10">
        <button type="submit" name="publish" value="0" class="btn btn--outline btn--block">Lưu nháp</button>
        <button type="submit" name="publish" value="1" class="btn btn--block">Công bố</button>
      </div>
    </form>
  </div>
  <?php endif; ?>
</div>
<?php admin_end();
