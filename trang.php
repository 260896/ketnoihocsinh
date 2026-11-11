<?php
/**
 * Trang thông tin công khai: ?p=chinh-sach | quy-tac | an-danh | gio-tiep-nhan
 * NỘI DUNG LÀ BẢN NHÁP (task #12) – PM và nhà trường duyệt lại câu chữ trước khi dùng thật.
 * Nguyên tắc: không hứa "bảo mật tuyệt đối"; nói rõ ai xem được gì và khi nào thông tin được chuyển đi.
 */
require __DIR__ . '/includes/bootstrap.php';

$authorized = array_column(array_filter(staff_all(), fn ($u) => can_reveal($u) && !$u['locked']), 'name');
$who = $authorized ? implode(' và ', $authorized) : 'người được nhà trường chỉ định';
$cfg = config();

$pages = [
    'chinh-sach' => ['Chính sách bảo mật', [
        ['Chúng tôi lưu những gì', [
            'Tài khoản: tên hiển thị (có thể là biệt danh), email, mật khẩu đã mã hóa, tiến độ học tập.',
            'Phiếu phản ánh: nội dung em viết, thời gian, địa điểm, tệp đính kèm và các tin nhắn trao đổi.',
            'Nhật ký thao tác của cán bộ: ai xem, phân loại, trả lời, mở danh tính, tải tệp gốc, lúc nào.',
            'Không thu thập họ tên thật, mã học sinh hay lớp nếu em không tự ghi vào phiếu.',
        ]],
        ['Ai xem được', [
            'Cán bộ phụ trách nhóm vấn đề đọc nội dung phiếu để trả lời em.',
            'Với phiếu ẩn danh, cán bộ xử lý không thấy tên và email của em.',
            'Chỉ ' . $who . ' mở được danh tính, phải ghi lý do và mọi lần mở đều được ghi nhật ký.',
            'Giáo viên quản lý học liệu và quản trị kỹ thuật không đọc phiếu.',
        ]],
        ['Tệp đính kèm', [
            'Ảnh JPG/PNG em gửi được tạo bản sao đã xóa vị trí GPS, thông tin máy chụp và thời điểm chụp; cán bộ xem bản sao này.',
            'Bản gốc chỉ người được chỉ định xem khi cần xác minh, và mỗi lần tải đều ghi nhật ký.',
            'Tệp PDF và video MP4 hiện chưa xóa được thông tin ẩn – em cân nhắc trước khi gửi.',
        ]],
        ['Email', [
            'Email hệ thống gửi em chỉ ghi "Phiếu có cập nhật", không chứa nội dung phiếu.',
        ]],
        ['Giới hạn', [
            'Ẩn danh là ẩn ở phần hiển thị và phân quyền. Hệ thống vẫn lưu liên kết giữa phiếu và tài khoản ở nơi riêng; không có hệ thống nào bảo đảm an toàn tuyệt đối.',
        ]],
    ]],
    'quy-tac' => ['Quy tắc sử dụng', [
        ['Khi gửi phản ánh', [
            'Viết đúng sự việc em biết hoặc chứng kiến. Không bịa đặt, không dùng kênh này để trêu chọc hay trả đũa người khác.',
            'Không đăng ảnh, video nhạy cảm của người khác. Bằng chứng chỉ gửi cho cán bộ qua phiếu, không phát tán.',
            'Hệ thống không tự kết luận ai là người vi phạm. Nhà trường xác minh trước khi xử lý.',
        ]],
        ['Khi học', [
            'Câu hỏi và câu trả lời mở được giáo viên đọc trước khi công bố. Không ghi tên thật của bạn bè.',
            'Huy hiệu chỉ dành cho việc học, không gắn với việc gửi phản ánh.',
        ]],
        ['Tài khoản', [
            'Mỗi học sinh dùng một tài khoản đăng ký bằng mã tham gia và email nhà trường cấp. Không chia sẻ mật khẩu.',
            'Tài khoản vi phạm quy tắc có thể bị khóa.',
        ]],
        ['Khi khẩn cấp', [
            DANGER_ADVICE,
            'Số hỗ trợ trên website: ' . hotline_text() . '.',
        ]],
    ]],
    'an-danh' => ['Cơ chế ẩn danh', [
        ['Ẩn danh nghĩa là gì', [
            'Khi chọn "Ẩn danh", cán bộ xử lý phiếu chỉ thấy nội dung, không thấy tên hiển thị và email của em.',
            'Em vẫn trao đổi được với cán bộ qua hộp thư gắn với mã phiếu.',
            'Liên kết giữa phiếu và tài khoản được lưu tách riêng khỏi nội dung phiếu.',
        ]],
        ['Ai mở được danh tính', [
            'Chỉ ' . $who . '.',
            'Chỉ khi có lý do chính đáng cần cho việc xác minh, xử lý vụ việc hoặc bảo đảm an toàn của em.',
            'Mỗi lần mở phải chọn căn cứ, ghi lý do; hệ thống ghi lại ai mở, lúc nào, phiếu nào.',
        ]],
        ['Khi nào thông tin được chuyển đi', [
            'Khi có nguy cơ gây hại cho em hoặc người khác, nhà trường có thể báo gia đình hoặc cơ quan chức năng.',
            'Khi cơ quan có thẩm quyền yêu cầu theo quy định pháp luật.',
            'Thầy cô sẽ báo em trước khi chuyển thông tin, trừ khi việc báo trước làm em gặp nguy hiểm hơn.',
        ]],
        ['Điều chúng tôi không hứa', [
            'Không có hệ thống nào an toàn tuyệt đối. Nếu chi tiết trong phiếu (thời gian, địa điểm, sự việc) chỉ một mình em biết, người đọc vẫn có thể đoán ra.',
        ]],
    ]],
    'gio-tiep-nhan' => ['Giờ tiếp nhận', [
        ['Giờ trực', [
            'Bộ phận tiếp nhận làm việc ' . duty_label() . '.',
            'Phiếu cần phản hồi ngay: cán bộ xác nhận trong ' . $cfg['urgent_ack_minutes'] . ' phút trong giờ trực; quá hạn hệ thống nhắc và chuyển người dự phòng.',
            'Phiếu thường: phản hồi lần đầu trong ' . ($cfg['first_reply_days'] ?? 2) . ' ngày làm việc.',
        ]],
        ['Ngoài giờ', [
            'Em vẫn gửi phiếu được; hệ thống ghi nhận và báo thời gian dự kiến xử lý.',
            'Không có người trực 24/7. Số hỗ trợ: ' . hotline_text() . '.',
            DANGER_ADVICE,
        ]],
    ]],
];

$p = array_key_exists(get('p'), $pages) ? get('p') : 'an-danh';
[$title, $sections] = $pages[$p];
$back = current_user() && current_user()['role'] === 'student' ? url('sv/tai-khoan.php') : url('');

mobile_start($title, ['back' => $back]);
?>
<?php if (SITE['is_demo']): ?>
  <div class="demo-box">Bản nháp – nội dung chờ nhà trường duyệt.</div>
<?php endif; ?>
<nav class="row wrap gap-8" aria-label="Trang thông tin">
  <?php foreach ($pages as $k => [$t]): ?>
    <a href="<?= e(url('trang.php', ['p' => $k])) ?>" class="chip<?= $k === $p ? ' chip--active' : '' ?>"<?= $k === $p ? ' aria-current="page"' : '' ?>><?= e($t) ?></a>
  <?php endforeach; ?>
</nav>
<?php foreach ($sections as [$h, $items]): ?>
  <section class="card" style="gap:8px">
    <h2 class="t-h2"><?= e($h) ?></h2>
    <ul style="margin:0;padding-left:20px" class="stack gap-6">
      <?php foreach ($items as $it): ?><li><?= e($it) ?></li><?php endforeach; ?>
    </ul>
  </section>
<?php endforeach; ?>
<?= emergency_card() ?>
<p class="t-small t-muted">Đơn vị quản lý: <?= e(SITE['org_name']) ?> · <?= e(SITE['domain']) ?></p>
<?php mobile_end();
