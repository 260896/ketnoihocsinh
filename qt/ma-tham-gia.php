<?php
/* QT10 – Mã tham gia (chỉ Quản trị viên)
 * Nhà trường nạp sẵn các cặp (mã, email) vào data/codes.json.
 * Học sinh chỉ đăng ký được khi nhập đúng mã + email của một cặp chưa dùng; mỗi cặp dùng 1 lần.
 */
require __DIR__ . '/../includes/bootstrap.php';
$me = require_staff(true);

const CODE_FILE_MAX_KB = 1024; // dung lượng tệp nạp tối đa
const CODE_LIST_MAX = 300;     // số dòng hiển thị tối đa

$filter = in_array(get('loc'), ['chua-dung', 'da-dung'], true) ? get('loc') : 'tat-ca';
$q = mb_strtolower(get('q'));

/** Lọc theo trạng thái và từ khóa (tìm trong mã hoặc email). */
$pick = function (array $codes, string $filter, string $q): array {
    return array_values(array_filter($codes, fn ($c) =>
        ($filter === 'tat-ca' || ($filter === 'da-dung') === $c['used'])
        && ($q === '' || str_contains(mb_strtolower($c['code']), $q) || str_contains($c['email'] ?? '', $q))));
};

/**
 * Đọc các dòng "mã,email" hoặc chỉ "email" (hệ thống tự sinh mã).
 * Ngăn cách bằng dấu phẩy, chấm phẩy hoặc tab. Thứ tự cột không bắt buộc: cột có @ là email.
 */
$parse = function (string $text): array {
    $text = (string) preg_replace('/^\xEF\xBB\xBF/', '', $text);
    $out = [];
    foreach (preg_split('/\r\n|\r|\n/', $text) as $i => $line) {
        $cells = array_values(array_filter(array_map('trim', preg_split('/[,;\t]/', $line)), fn ($x) => $x !== ''));
        if (!$cells) continue;
        $email = '';
        $code = '';
        foreach ($cells as $cell) {
            if (str_contains($cell, '@')) { if ($email === '') $email = $cell; }
            elseif ($code === '') $code = $cell;
        }
        // Dòng tiêu đề (vd "ma,email") → bỏ
        if ($email === '' && preg_match('/email/i', $line)) continue;
        $out[] = ['code' => $code, 'email' => $email];
    }
    return $out;
};

/* ---------- Tải danh sách (CSV) ---------- */
if (get('tai') === '1') {
    $rows = $pick(join_codes_all(), $filter, $q);
    audit_log('Mã tham gia', null, 'Tải danh sách ' . count($rows) . ' mã (' . $filter . ')', $me);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="ma-tham-gia-' . $filter . '-' . date('Ymd-His') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // BOM để Excel đọc đúng UTF-8
    fputcsv($out, ['ma_tham_gia', 'email', 'trang_thai', 'ngay_nap', 'ngay_dung']);
    foreach ($rows as $c) {
        fputcsv($out, [$c['code'], $c['email'] ?? '', $c['used'] ? 'Đã dùng' : 'Chưa dùng', fmt_dt($c['created_at']), $c['used_at'] ? fmt_dt($c['used_at']) : '']);
    }
    exit;
}

$err = [];
$v = ['codes' => ''];

if (is_post()) {
    $a = post('action');

    if ($a === 'import') {
        $v['codes'] = post('codes');
        $list = $parse($v['codes']);

        $f = $_FILES['file'] ?? null;
        if ($f && $f['error'] !== UPLOAD_ERR_NO_FILE) {
            $ext = strtolower(pathinfo((string) $f['name'], PATHINFO_EXTENSION));
            if ($f['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) {
                $err['file'] = 'Không tải được tệp. Thử lại.';
            } elseif (!in_array($ext, ['csv', 'txt'], true)) {
                $err['file'] = 'Chỉ nhận tệp .csv hoặc .txt.';
            } elseif ($f['size'] > CODE_FILE_MAX_KB * 1024) {
                $err['file'] = 'Tệp quá lớn (tối đa ' . CODE_FILE_MAX_KB . ' KB).';
            } else {
                $list = array_merge($list, $parse((string) file_get_contents($f['tmp_name'])));
            }
        }

        if (!$err && !$list) $err['codes'] = 'Dán danh sách hoặc chọn tệp để nạp.';
        if (!$err) {
            [$added, $skipped] = join_codes_add($list);
            audit_log('Mã tham gia', null, 'Nạp ' . count($added) . ' cặp mã + email' . ($skipped ? ', bỏ qua ' . count($skipped) : ''), $me);
            flash('Đã nạp ' . count($added) . ' cặp mã + email.');
            if ($skipped) {
                flash('Bỏ qua ' . count($skipped) . ' dòng: ' . implode('; ', array_slice($skipped, 0, 8)) . (count($skipped) > 8 ? '…' : ''), 'error');
            }
            // Hiện lại các mã hệ thống vừa tự sinh để quản trị viên gửi cho học sinh
            $auto = array_filter($added, fn ($r) => !in_array($r['code'], array_map(fn ($x) => join_code_normalize($x['code']), $list), true));
            if ($auto) $_SESSION['new_codes'] = array_values($auto);
            redirect('qt/ma-tham-gia.php', ['loc' => 'chua-dung']);
        }
    }

    if ($a === 'set_email') {
        $msg = join_code_set_email(post('code'), post('email'));
        if ($msg) {
            flash($msg, 'error');
        } else {
            audit_log('Mã tham gia', null, 'Đổi email của mã ' . join_code_normalize(post('code')), $me);
            flash('Đã cập nhật email cho mã ' . join_code_normalize(post('code')) . '.');
        }
        redirect('qt/ma-tham-gia.php', ['loc' => $filter === 'tat-ca' ? null : $filter, 'q' => $q]);
    }

    if ($a === 'delete') {
        if (join_code_delete(post('code'))) {
            audit_log('Mã tham gia', null, 'Xóa mã tham gia ' . join_code_normalize(post('code')), $me);
            flash('Đã xóa mã ' . join_code_normalize(post('code')) . '.');
        } else {
            flash('Không xóa được: mã không tồn tại hoặc đã được dùng.', 'error');
        }
        redirect('qt/ma-tham-gia.php', ['loc' => $filter === 'tat-ca' ? null : $filter, 'q' => $q]);
    }
}

$all = join_codes_all();
$usedCount = count(array_filter($all, fn ($c) => $c['used']));
$rows = $pick($all, $filter, $q);
usort($rows, fn ($x, $y) => [$x['used'], $y['created_at'], $x['code']] <=> [$y['used'], $x['created_at'], $y['code']]);
$newCodes = $_SESSION['new_codes'] ?? [];
unset($_SESSION['new_codes']);

$chip = fn (string $key, string $label, int $n) =>
    '<a href="' . e(url('qt/ma-tham-gia.php', ['loc' => $key === 'tat-ca' ? null : $key, 'q' => $q])) . '" class="chip' . ($filter === $key ? ' chip--active' : '') . '">' . e($label) . ' · ' . $n . '</a>';

admin_start('Mã tham gia', 'Mã tham gia', 'Học sinh chỉ đăng ký được khi nhập đúng cặp mã + email có trong danh sách. Mỗi cặp dùng một lần.',
    '<a href="' . e(url('qt/ma-tham-gia.php', ['loc' => $filter === 'tat-ca' ? null : $filter, 'q' => $q, 'tai' => '1'])) . '" class="btn btn--outline">Tải danh sách (CSV)</a>');
?>
<div class="a-cols">
  <div class="a-col-main">
    <?php if ($newCodes): ?>
      <div class="card card--admin">
        <p class="t-h2">Mã hệ thống vừa sinh (<?= count($newCodes) ?>)</p>
        <p class="t-small t-muted">Gửi mã cho từng học sinh theo email tương ứng. Danh sách này chỉ hiện một lần; sau đó xem ở bộ lọc "Chưa dùng" hoặc tải CSV.</p>
        <textarea class="textarea" rows="6" readonly aria-label="Mã vừa sinh" onclick="this.select()"><?= e(implode("\n", array_map(fn ($r) => $r['code'] . ',' . $r['email'], $newCodes))) ?></textarea>
      </div>
    <?php endif; ?>

    <div class="row wrap gap-8">
      <?= $chip('tat-ca', 'Tất cả', count($all)) ?>
      <?= $chip('chua-dung', 'Chưa dùng', count($all) - $usedCount) ?>
      <?= $chip('da-dung', 'Đã dùng', $usedCount) ?>
    </div>

    <form method="get" class="row gap-10 items-end">
      <?php if ($filter !== 'tat-ca'): ?><input type="hidden" name="loc" value="<?= e($filter) ?>"><?php endif; ?>
      <div class="grow"><?= field_input('q', 'Tìm mã hoặc email', get('q'), ['placeholder' => 'Nhập một phần mã hoặc email']) ?></div>
      <button type="submit" class="btn btn--outline">Tìm</button>
    </form>

    <div class="table-wrap">
      <table class="table">
        <thead><tr><th scope="col" style="width:140px">Mã tham gia</th><th scope="col">Email</th><th scope="col" style="width:110px">Trạng thái</th><th scope="col" style="width:140px">Ngày dùng</th><th scope="col" style="width:110px"><span class="sr-only">Thao tác</span></th></tr></thead>
        <tbody>
        <?php foreach (array_slice($rows, 0, CODE_LIST_MAX) as $c): ?>
          <tr>
            <td><code><?= e($c['code']) ?></code></td>
            <td><?= ($c['email'] ?? '') !== '' ? e($c['email']) : '<span class="badge badge--danger">Chưa có email</span>' ?></td>
            <td><?= $c['used'] ? '<span class="badge badge--muted">Đã dùng</span>' : '<span class="badge badge--info">Chưa dùng</span>' ?></td>
            <td><?= $c['used_at'] ? e(fmt_dt($c['used_at'], 'd/m/Y H:i')) : '—' ?></td>
            <td>
              <?php if (!$c['used']): ?>
                <details class="disclosure">
                  <summary class="link">Sửa</summary>
                  <form method="post" class="card" style="position:absolute;right:32px;z-index:3;width:300px;box-shadow:var(--shadow-soft)">
                    <?= csrf_field() ?><input type="hidden" name="action" value="set_email"><input type="hidden" name="code" value="<?= e($c['code']) ?>">
                    <p class="t-title">Mã <?= e($c['code']) ?></p>
                    <?= field_input('email', 'Email', $c['email'] ?? '', ['id' => 'em-' . $c['code'], 'type' => 'email', 'required' => true]) ?>
                    <button type="submit" class="btn btn--sm">Lưu</button>
                  </form>
                </details>
                <?= post_button('Xóa', ['action' => 'delete', 'code' => $c['code']], 'link link--danger') ?>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="5" class="table-empty"><?= $all ? 'Không có mã phù hợp.' : 'Chưa nạp mã nào. Học sinh chưa thể đăng ký.' ?></td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
    <?php if (count($rows) > CODE_LIST_MAX): ?>
      <p class="t-small t-muted">Đang hiển thị <?= CODE_LIST_MAX ?>/<?= count($rows) ?> mã. Dùng ô tìm kiếm hoặc tải CSV để xem đầy đủ.</p>
    <?php endif; ?>
  </div>

  <div class="a-col-side" style="flex-basis:380px">
    <form method="post" enctype="multipart/form-data" class="card card--admin">
      <?= csrf_field() ?><input type="hidden" name="action" value="import">
      <p class="t-h2">Nạp mã + email</p>
      <?= field_textarea('codes', 'Mỗi dòng: mã,email – hoặc chỉ email để hệ thống tự sinh mã', $v['codes'], ['rows' => 7, 'placeholder' => "HS-0001,an.nguyen@email.com\nHS-0002,binh.tran@email.com\nchi.le@email.com", 'error' => $err['codes'] ?? null]) ?>
      <?= field_input('file', 'Hoặc chọn tệp .csv / .txt (cột mã và cột email)', '', ['type' => 'file', 'accept' => '.csv,.txt', 'error' => $err['file'] ?? null]) ?>
      <p class="t-small t-muted">Mã gồm 4–32 ký tự: chữ không dấu, số, <code>-</code> hoặc <code>_</code>; không phân biệt hoa thường. Mỗi email chỉ được cấp một mã. Dòng có mã đã tồn tại hoặc email đã được cấp mã sẽ bị bỏ qua.</p>
      <button type="submit" class="btn btn--block">Nạp</button>
    </form>
  </div>
</div>
<?php admin_end();
