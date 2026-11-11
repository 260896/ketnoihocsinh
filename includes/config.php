<?php
/**
 * Thông tin hiển thị cố định của website.
 * GIAI ĐOẠN CHẠY THỬ: toàn bộ tên trường, nhân sự, email, số điện thoại là GIẢ LẬP.
 *
 * Các thông số vận hành (giờ trực, số khẩn cấp, mã tham gia, đầu mối…) được
 * chỉnh trong trang quản trị "Cấu hình đầu mối" và lưu ở data/config.json.
 */
const SITE = [
    'project_name' => 'KẾT NỐI HỌC SINH',
    'name_first'   => 'KẾT NỐI',     // phần chữ xanh của tên chữ (thay logo)
    'name_second'  => 'HỌC SINH',    // phần chữ đỏ
    'slogan'       => 'Lắng nghe – Đồng hành – Chia sẻ',
    'intro'        => 'Không gian để học sinh tìm hiểu kiến thức, tham gia các nội dung học tập và gửi phản ánh khi gặp vấn đề trong quá trình học tập, sinh hoạt tại trường.',
    'org_name'     => 'Trường Trung học ABC',
    'unit'         => 'nhà trường',
    'domain'       => 'ketnoihocsinh.local',
    'hotline'      => '1900 0000',   // dùng khi chưa nhập số ở trang Cấu hình
    'verify_ttl'   => '24 giờ',
    'staff_domain' => 'abc-trung học.edu.vn',
    'is_demo'      => true,          // hiện dòng "Bản chạy thử – dữ liệu giả lập"
];

/** Mật khẩu chung của các tài khoản chạy thử (học sinh và cán bộ). */
const DEMO_PASSWORD = 'TestHS2026!';

const ISSUE_UNKNOWN = 'Chưa rõ / Khác';

const TICKET_STATUSES = ['Đã tiếp nhận', 'Cần bổ sung', 'Đang xem xét', 'Đã có phản hồi'];
const PRIORITIES      = ['Khẩn', 'Cao', 'Thường'];
const REQUEST_TYPES   = [
    'Phản ánh'       => 'Báo về một sự việc đã xảy ra',
    'Đề nghị hỗ trợ' => 'Bạn hoặc người khác cần được giúp đỡ',
    'Góp ý'          => 'Ý kiến về hoạt động, môi trường học đường, website',
];

/** Icon được phép chọn cho chủ đề học. */
const TOPIC_ICONS = [
    'shield'       => 'Khiên (an toàn)',
    'vapeOff'      => 'Cấm thuốc lá',
    'messageLines' => 'Tin nhắn',
    'heart'        => 'Trái tim (gia đình)',
    'hand'         => 'Bàn tay (ranh giới)',
    'book'         => 'Sách',
    'users'        => 'Nhóm người',
    'alert'        => 'Cảnh báo',
];
