<?php
/* QT03 – Xử lý phiếu, kèm QT04 – Form mở danh tính (?id=...&mo-danh-tinh=1) */
require __DIR__ . '/../includes/bootstrap.php';
$me = require_staff('tickets');

$t = ticket_find(get('id'));
if (!$t) redirect('qt/phieu.php');
if (!can_access_ticket($me, $t)) {
    flash('Phiếu này không thuộc nhóm bạn phụ trách.', 'error');
    redirect('qt/phieu.php');
}
$handlerIds = array_column(array_filter(staff_all(), fn ($u) => can_handle_tickets($u) && !$u['locked']), 'id');
$cfg = config();
$showReveal = get('mo-danh-tinh') === '1';
$revealErr = [];
$replyText = '';

if (is_post()) {
    $a = post('action');

    // Lưu phân loại & phân công
    if ($a === 'save') {
        $changes = [];
        $group = in_array(post('group'), [...issue_groups(), ISSUE_UNKNOWN], true) ? post('group') : $t['group'];
        $priority = in_array(post('priority'), PRIORITIES, true) ? post('priority') : $t['priority'];
        $status = in_array(post('status'), TICKET_STATUSES, true) ? post('status') : $t['status'];
        // Chỉ cán bộ xử lý phiếu mới được phân công (không chọn được tài khoản học sinh / giáo viên)
        $assignee = in_array(post('assignee'), $handlerIds, true) ? post('assignee') : null;
        $deadline = post('deadline') !== '' ? date('c', (int) strtotime(post('deadline'))) : null;

        if ($group !== $t['group']) {
            $changes[] = "phân loại → $group";
            audit_log('Phân loại', $t['code'], ($t['group'] === ISSUE_UNKNOWN ? 'Chưa rõ nhóm' : $t['group']) . " → $group", $me);
        }
        if ($priority !== $t['priority']) {
            $changes[] = "ưu tiên {$t['priority']} → $priority";
            audit_log('Ưu tiên', $t['code'], "{$t['priority']} → $priority", $me);
        }
        if ($assignee !== $t['assignee']) {
            $changes[] = 'phân công → ' . staff_name($assignee);
            audit_log('Phân công', $t['code'], staff_name($t['assignee']) . ' → ' . staff_name($assignee), $me);
        }
        if ($status !== $t['status']) {
            $changes[] = "đổi trạng thái → $status";
            audit_log('Trạng thái', $t['code'], "{$t['status']} → $status", $me);
            $t['has_update'] = true;
        }
        $statusChanged = $status !== $t['status'];
        $assigneeChanged = $assignee !== $t['assignee'];
        $t = array_merge($t, compact('group', 'priority', 'status', 'assignee', 'deadline'));
        $t['urgent'] = $priority === PRIORITY_NOW ? true : $t['urgent'];
        if ($changes) array_unshift($t['history'], ['time' => now_iso(), 'text' => $me['name'] . ' ' . implode(', ', $changes)]);
        if ($statusChanged) $t = ticket_acknowledge($t, $me);
        ticket_save($t);
        if ($statusChanged) notify_student_update($t);
        if ($assigneeChanged && $assignee && $assignee !== $me['id']) notify_staff([$assignee], $t, 'Phiếu được phân công cho bạn');
        flash($changes ? 'Đã lưu thay đổi.' : 'Không có gì thay đổi.');
        redirect_back();
    }

    // Chèn mẫu trả lời vào ô soạn (không gửi)
    if ($a === 'use_template') {
        foreach ($cfg['templates'] as $tpl) {
            if ($tpl['id'] === post('template')) $replyText = $tpl['body'];
        }
        if ($replyText === '') $replyText = post('reply');
    }

    // Gửi phản hồi cho học sinh
    if ($a === 'reply') {
        if (post('reply') === '') {
            flash('Nhập nội dung phản hồi.', 'error');
            redirect_back();
        }
        $t['messages'][] = ['from' => 'staff', 'author_id' => $me['id'], 'time' => now_iso(), 'text' => post('reply')];
        array_unshift($t['history'], ['time' => now_iso(), 'text' => $me['name'] . ' gửi phản hồi']);
        $t['has_update'] = true;
        $t = ticket_acknowledge($t, $me);
        ticket_save($t);
        notify_student_update($t);
        audit_log('Phản hồi', $t['code'], 'Gửi tin nhắn trong hộp thư', $me);
        flash('Đã gửi phản hồi. Học sinh nhận email “phiếu có cập nhật”.');
        redirect('qt/xu-ly-phieu.php', ['id' => $t['code']]);
    }

    // Xác nhận đã nhận phiếu (dừng đồng hồ 15 phút của phiếu cần phản hồi ngay)
    if ($a === 'ack') {
        ticket_save(ticket_acknowledge($t, $me));
        flash('Đã xác nhận nhận phiếu.');
        redirect('qt/xu-ly-phieu.php', ['id' => $t['code']]);
    }

    // Mở danh tính (QT04)
    if ($a === 'reveal') {
        $showReveal = true;
        if (!can_reveal($me)) $revealErr['perm'] = 'Bạn không phải người được chỉ định mở danh tính.';
        if (post('basis') === '') $revealErr['basis'] = 'Chọn căn cứ theo quy tắc.';
        if (post('reason') === '') $revealErr['reason'] = 'Nhập lý do cụ thể.';
        if (empty($_POST['confirm'])) $revealErr['confirm'] = 'Bạn cần xác nhận.';
        if (!$revealErr) {
            $t['revealed_to'] = array_values(array_unique([...$t['revealed_to'], $me['id']]));
            array_unshift($t['history'], ['time' => now_iso(), 'text' => $me['name'] . ' mở danh tính người gửi']);
            ticket_save($t);
            audit_log('Mở danh tính', $t['code'], 'Căn cứ: ' . post('basis') . ' · Lý do: ' . post('reason'), $me);
            flash('Đã mở danh tính. Thao tác đã được ghi nhật ký.');
            redirect('qt/xu-ly-phieu.php', ['id' => $t['code']]);
        }
    }
}

$canSeeIdentity = !$t['anonymous'] || in_array($me['id'], $t['revealed_to'], true);
// Chỉ tra liên kết người gửi khi được phép thấy danh tính (liên kết lưu riêng ở ticket_owners.json)
$owner = $canSeeIdentity ? user_find(ticket_owner_id($t['code'])) : null;
$staffOpts = ['' => 'Hàng chờ phân loại'] + array_column(array_filter(staff_all(), fn ($u) => in_array($u['id'], $handlerIds, true)), 'name', 'id');
$templates = array_filter($cfg['templates'], fn ($tpl) => empty($tpl['group']) || $tpl['group'] === $t['group']);
$similar = ticket_similar($t);
// Căn cứ mở danh tính: chỉ khi có lý do chính đáng, cần thiết cho xác minh và xử lý vụ việc
$basisOptions = ['' => 'Chọn căn cứ'] + array_combine($b = [
    'Cần thiết để xác minh sự việc',
    'Cần liên hệ trực tiếp để bảo đảm an toàn cho học sinh',
    'Theo yêu cầu của cơ quan có thẩm quyền',
    'Lý do chính đáng khác (ghi rõ ở ô lý do)',
], $b);

admin_start(
    'Phiếu phản ánh',
    'Phiếu #' . $t['code'],
    $t['type'] . ' · Gửi ' . fmt_dt($t['created_at']) . ' · ' . ($t['anonymous'] ? 'Ẩn danh' : 'Có danh tính'),
    '<a href="' . e(url('qt/phieu.php')) . '" class="btn btn--ghost">← Danh sách</a><a href="#mau-tra-loi" class="btn btn--outline">Dùng mẫu trả lời</a>'
);
?>
<div class="a-cols">
  <div class="a-col-main">
    <div class="card card--admin">
      <div class="row wrap gap-8">
        <?= status_badge($t['status']) ?><?= priority_badge($t['priority']) ?>
        <?php if ($t['anonymous']): ?>
          <span class="badge badge--muted badge--square row gap-6" style="padding:4px 8px"><?= icon('eyeOff', 14, 'var(--c-muted)') ?><?= $canSeeIdentity ? 'Ẩn danh – bạn đã mở danh tính' : 'Ẩn danh – danh tính bị ẩn' ?></span>
        <?php endif; ?>
      </div>
      <?php if ($canSeeIdentity && $owner): ?>
        <div class="info-row"><p>Người gửi</p><p class="t-strong"><?= e($owner['name']) ?> · <?= e($owner['email']) ?></p></div>
      <?php endif; ?>
      <div class="info-row"><p>Mô tả</p><p class="pre"><?= e($t['description']) ?></p></div>
      <div class="info-row"><p>Nhóm vấn đề</p><p><?= e($t['group']) ?></p></div>
      <div class="info-row"><p>Thời gian</p><p><?= e(fmt_dt($t['occurred_at'] ?: null)) ?></p></div>
      <div class="info-row"><p>Địa điểm</p><p><?= e($t['location'] ?: '—') ?></p></div>
      <div class="info-row"><p>Cần hỗ trợ ngay</p><p><?= $t['urgent'] ? 'Có' : 'Không' ?></p></div>
      <?php foreach ($t['attachments'] as $i => $att): ?>
        <div class="panel" style="padding:12px">
          <div class="row wrap gap-12">
            <?= icon('file', 20, 'var(--c-primary)') ?>
            <div style="flex:1 1 200px">
              <p class="t-small t-strong"><?= e($att['name']) ?> · <?= e($att['size']) ?></p>
              <p class="t-small t-muted"><?= !empty($att['clean_path']) ? 'Đã có bản xóa siêu dữ liệu (GPS, máy chụp, thời điểm chụp)' : 'Loại tệp này chưa xóa được siêu dữ liệu' ?></p>
            </div>
            <?php if (!empty($att['clean_path'])): ?>
              <a href="<?= e(url('tep.php', ['k' => 'att', 't' => $t['code'], 'i' => $i])) ?>" class="btn btn--sm" target="_blank">Xem</a>
            <?php endif; ?>
            <?php if (can_view_original($me)): ?>
              <a href="<?= e(url('tep.php', ['k' => 'att', 't' => $t['code'], 'i' => $i, 'goc' => 1])) ?>" class="btn btn--outline btn--sm" target="_blank">Xem bản gốc (ghi nhật ký)</a>
            <?php else: ?>
              <span class="t-small t-muted">Bản gốc: chỉ cán bộ có thẩm quyền</span>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="card card--admin" style="gap:12px">
      <p class="t-h2">Hộp thư theo mã phiếu</p>
      <?php foreach ($t['messages'] as $m):
          $isStaff = $m['from'] === 'staff';
          $meta = match ($m['from']) {
              'system'  => 'Xác nhận tự động',
              'staff'   => ($m['author_id'] === $me['id'] ? 'Bạn' : 'Cán bộ') . ' · ' . staff_name($m['author_id']),
              default   => 'Học sinh' . ($t['anonymous'] && !$canSeeIdentity ? ' (ẩn danh)' : ''),
          }; ?>
        <div class="msg<?= $isStaff ? ' msg--me' : '' ?>">
          <div class="bubble <?= $m['from'] === 'system' ? 'bubble--system' : ($isStaff ? 'bubble--me' : 'bubble--other') ?>">
            <p class="bubble__meta"><?= e($meta) ?> · <?= e(fmt_dt($m['time'])) ?></p>
            <?php if ($m['text'] !== ''): ?><p class="pre"><?= e($m['text']) ?></p><?php endif; ?>
            <?php if (isset($m['attachment'], $t['attachments'][$m['attachment']])): ?>
              <a class="t-small row gap-6" href="<?= e(url('tep.php', ['k' => 'att', 't' => $t['code'], 'i' => $m['attachment']])) ?>" target="_blank"><?= icon('paperclip', 14) ?><?= e($t['attachments'][$m['attachment']]['name']) ?></a>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>

      <form method="post" class="stack gap-12" id="mau-tra-loi">
        <?= csrf_field() ?>
        <?= field_textarea('reply', 'Trả lời học sinh', $replyText, ['placeholder' => 'Nhập phản hồi hoặc chọn mẫu trả lời…']) ?>
        <div class="row wrap gap-10 items-end">
          <div style="flex:1 1 240px"><?= field_select('template', 'Mẫu trả lời', ['' => 'Chọn mẫu trả lời'] + array_column($templates, 'name', 'id'), post('template')) ?></div>
          <button type="submit" name="action" value="use_template" class="btn btn--outline">Chèn mẫu</button>
        </div>
        <div class="row wrap gap-12">
          <p class="t-small t-muted" style="flex:1 1 200px">Học sinh chỉ nhận email “phiếu có cập nhật”, không kèm nội dung.</p>
          <button type="submit" name="action" value="reply" class="btn">Gửi phản hồi</button>
        </div>
      </form>
    </div>
  </div>

  <div class="a-col-side">
    <?php [$dl, $dlAlert] = deadline_info($t); ?>
    <div class="card card--admin" style="gap:10px">
      <p class="t-h2">Hạn xử lý</p>
      <p class="<?= $dlAlert ? 't-danger t-strong' : '' ?>"><?= e($dl) ?></p>
      <?php if (!empty($t['acknowledged_at'])): ?>
        <p class="t-small t-muted">Đã xác nhận nhận phiếu lúc <?= e(fmt_dt($t['acknowledged_at'])) ?>.</p>
      <?php elseif ($t['status'] !== 'Đã có phản hồi'): ?>
        <p class="t-small t-muted"><?= $t['urgent'] ? 'Phiếu cần phản hồi ngay: xác nhận trong ' . e($cfg['urgent_ack_minutes']) . ' phút, quá hạn hệ thống nhắc và chuyển người dự phòng.' : 'Chưa có cán bộ xác nhận.' ?></p>
        <?= post_button('Xác nhận đã nhận phiếu', ['action' => 'ack'], 'btn btn--block' . ($t['urgent'] ? ' btn--danger' : ' btn--outline')) ?>
      <?php endif; ?>
    </div>

    <?php if ($similar): ?>
      <div class="card card--admin" style="gap:8px">
        <p class="t-h2">Phiếu có thể liên quan</p>
        <p class="t-small t-muted">Gợi ý theo nhóm, thời gian và từ ngữ giống nhau. Hệ thống không tự gộp, đóng hay xóa phiếu.</p>
        <?php foreach ($similar as ['ticket' => $o]): ?>
          <?php if (can_access_ticket($me, $o)): ?>
            <a href="<?= e(url('qt/xu-ly-phieu.php', ['id' => $o['code']])) ?>" class="t-small">#<?= e($o['code']) ?> · <?= e(fmt_dt($o['created_at'], 'd/m/Y')) ?> · <?= e($o['status']) ?></a>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="post" class="card card--admin">
      <?= csrf_field() ?><input type="hidden" name="action" value="save">
      <p class="t-h2">Phân loại &amp; phân công</p>
      <?= field_select('group', 'Nhóm vấn đề', [...issue_groups(), ISSUE_UNKNOWN], $t['group']) ?>
      <?= field_select('priority', 'Mức ưu tiên', PRIORITIES, $t['priority']) ?>
      <?= field_select('status', 'Trạng thái', TICKET_STATUSES, $t['status']) ?>
      <?= field_select('assignee', 'Người phụ trách', $staffOpts, (string) $t['assignee']) ?>
      <?= field_input('deadline', 'Hạn xử lý', $t['deadline'] ? date('Y-m-d\TH:i', strtotime($t['deadline'])) : '', ['type' => 'datetime-local']) ?>
      <button type="submit" class="btn btn--block">Lưu thay đổi</button>
    </form>

    <?php if ($t['anonymous']): ?>
      <div class="panel panel--danger" style="border-radius:14px;padding:20px">
        <div class="row gap-8"><?= icon('lock', 18, 'var(--c-danger)') ?><p class="t-title t-danger">Danh tính người gửi</p></div>
        <?php if ($canSeeIdentity): ?>
          <p class="t-small">Bạn đã mở danh tính phiếu này. Lần mở đã được ghi nhật ký.</p>
        <?php else: ?>
          <?php $authorized = array_column(array_filter(staff_all(), fn ($u) => can_reveal($u) && !$u['locked']), 'name'); ?>
          <p class="t-small">Đang ẩn. Chỉ <strong><?= e(implode(' và ', $authorized) ?: 'người được chỉ định') ?></strong> được xem thông tin định danh, khi có lý do chính đáng cần cho xác minh, xử lý vụ việc. Mỗi lần mở đều ghi nhật ký.</p>
          <?php if (can_reveal($me)): ?>
            <a href="<?= e(url('qt/xu-ly-phieu.php', ['id' => $t['code'], 'mo-danh-tinh' => 1])) ?>" class="btn btn--danger-outline btn--block">Mở danh tính (ghi nhật ký)</a>
          <?php else: ?>
            <p class="t-small t-muted">Bạn là người xử lý phản ánh nên không xem được danh tính người gửi.</p>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="card card--admin" style="gap:10px">
      <p class="t-h2">Nhật ký phiếu</p>
      <?php foreach ($t['history'] as $h): ?>
        <div class="row gap-10 items-start t-small"><p class="t-muted" style="flex:0 0 auto"><?= e(fmt_dt($h['time'], 'd/m H:i')) ?></p><p><?= e($h['text']) ?></p></div>
      <?php endforeach; ?>
      <?php if (!$t['history']): ?><p class="t-small t-muted">Chưa có thao tác.</p><?php endif; ?>
    </div>
  </div>
</div>

<?php if ($showReveal && $t['anonymous'] && !$canSeeIdentity): ?>
  <!-- QT04 – Form mở danh tính -->
  <div class="modal-backdrop">
    <form method="post" class="modal" role="dialog" aria-modal="true" aria-labelledby="dlg">
      <?= csrf_field() ?><input type="hidden" name="action" value="reveal">
      <div class="row gap-10">
        <span class="icon-circle" style="width:40px;height:40px;background:var(--c-danger-50)"><?= icon('lock', 20, 'var(--c-danger)') ?></span>
        <h2 id="dlg" class="t-h2 grow">Mở danh tính phiếu #<?= e($t['code']) ?></h2>
        <a href="<?= e(url('qt/xu-ly-phieu.php', ['id' => $t['code']])) ?>" class="icon-btn" aria-label="Đóng"><?= icon('x', 20, 'var(--c-muted)') ?></a>
      </div>
      <p class="t-small t-muted">Chỉ dùng khi đúng trường hợp trong quy tắc mở danh tính. Thao tác này được ghi nhật ký kèm tên bạn, thời điểm và lý do.</p>
      <?php if (!can_reveal($me)): ?>
        <div class="panel panel--danger"><?= note('alert', 'Tài khoản của bạn chưa được cấp quyền mở danh tính. Liên hệ quản trị viên.', 'var(--c-danger)') ?></div>
      <?php endif; ?>
      <?= field_select('basis', 'Căn cứ mở danh tính *', $basisOptions, post('basis'), ['required' => true]) ?>
      <?= field_error($revealErr['basis'] ?? null, 'basis') ?>
      <?= field_textarea('reason', 'Lý do cụ thể *', post('reason'), ['placeholder' => 'Mô tả vì sao cần mở danh tính', 'required' => true, 'error' => $revealErr['reason'] ?? null]) ?>
      <label class="note" style="cursor:pointer">
        <input type="checkbox" name="confirm" value="1" class="checkbox" required>
        <span>Tôi là người được chỉ định và hiểu thao tác này được ghi nhật ký.</span>
      </label>
      <?= field_error($revealErr['confirm'] ?? null, 'confirm') ?>
      <div class="row gap-12 justify-end">
        <a href="<?= e(url('qt/xu-ly-phieu.php', ['id' => $t['code']])) ?>" class="btn btn--outline">Hủy</a>
        <button type="submit" class="btn btn--danger" <?= can_reveal($me) ? '' : 'disabled' ?>>Mở danh tính</button>
      </div>
    </form>
  </div>
<?php endif; ?>
<?php admin_end();
