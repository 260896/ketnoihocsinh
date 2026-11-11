<?php
/**
 * Thông tin hiển thị cố định của website.
 * GIAI ĐOẠN CHẠY THỬ: toàn bộ tên trường, nhân sự, email, số điện thoại là GIẢ LẬP.
 *
 * Các thông số vận hành (giờ trực, số khẩn cấp, đầu mối…) được chỉnh trong trang
 * quản trị "Cấu hình đầu mối" và lưu ở data/config.json. Mã tham gia + email ở data/codes.json.
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
    // Tài liệu gốc ghi "abc-trung học.edu.vn" (có dấu cách, không hợp lệ) → dùng abc-trunghoc.edu.vn
    'staff_domain' => 'abc-trunghoc.edu.vn',
    'is_demo'      => true,          // hiện dòng "Bản chạy thử – dữ liệu giả lập"
];

/** Mật khẩu chung của các tài khoản chạy thử (học sinh và cán bộ). */
const DEMO_PASSWORD = 'TestHS2026!';

const ISSUE_UNKNOWN = 'Chưa rõ / Khác';

const TICKET_STATUSES = ['Đã tiếp nhận', 'Cần bổ sung', 'Đang xem xét', 'Đã có phản hồi'];
/** 2 mức ưu tiên theo Brief. Cán bộ được nâng mức. */
const PRIORITY_NOW    = 'Cần phản hồi ngay';
const PRIORITY_LATER  = 'Phản hồi sau';
const PRIORITIES      = [PRIORITY_NOW, PRIORITY_LATER];

/**
 * Vai trò cán bộ:
 *  - Quản trị viên: cấu hình hệ thống, tài khoản, mã tham gia. Không đọc phiếu, trừ khi là người được chỉ định mở danh tính.
 *  - Xử lý phiếu: xem phiếu thuộc nhóm được giao (theo bảng đầu mối) hoặc được phân công.
 *  - Quản lý học liệu (giáo viên): soạn học liệu, trả lời hỏi đáp. Không xem phiếu.
 */
const ROLE_ADMIN    = 'Quản trị viên';
const ROLE_HANDLER  = 'Xử lý phiếu';
const ROLE_TEACHER  = 'Quản lý học liệu';
const STAFF_ROLES   = [ROLE_HANDLER, ROLE_TEACHER, ROLE_ADMIN];

/** Brief: chỉ 2 người được mở danh tính (Nguyễn Minh Anh, Võ Thanh Tùng). */
const REVEAL_MAX = 2;

/** Chống dò: số lần sai tối đa trong khoảng khóa (giây). */
const THROTTLE_MAX = 5;      // theo email / tài khoản
const THROTTLE_MAX_IP = 30;  // theo IP – cao hơn vì cả trường có thể dùng chung 1 IP (NAT)
const THROTTLE_WINDOW = 900;
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
