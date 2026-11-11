<?php
/* Mục lục màn hình – mã SVxx / QTxx khớp với bản thiết kế */
require __DIR__ . '/includes/bootstrap.php';

$groups = [
    'Landing page' => [
        ['—', 'Landing page', ''],
        ['—', 'Landing – ngoài giờ trực', '?ngoaigio=1'],
        ['—', 'Chính sách bảo mật / Quy tắc / Cơ chế ẩn danh / Giờ tiếp nhận', 'trang.php?p=an-danh'],
    ],
    'Học sinh – Tài khoản' => [
        ['SV01–02', 'Đăng ký (mã + email phải khớp danh sách ở QT10; thử: DEMO-0002 + demo2@example.com)', 'sv/dang-ky.php'],
        ['SV03', 'Xác thực email', 'sv/xac-thuc.php'],
        ['SV04', 'Đăng nhập', 'sv/dang-nhap.php'],
        ['SV05–06', 'Quên mật khẩu → Mật khẩu mới', 'sv/quen-mat-khau.php'],
    ],
    'Học sinh – Học (cần đăng nhập)' => [
        ['SV07', 'Trang chủ (Học)', 'sv/hoc.php'],
        ['SV08', 'Chủ đề', 'sv/chu-de.php?id=bao-luc-hoc-duong'],
        ['SV09', 'Tình huống & giải thích', 'sv/tinh-huong.php?chu-de=bao-luc-hoc-duong&so=1'],
        ['SV10', 'Hoàn thành chủ đề', 'sv/hoan-thanh.php?chu-de=bao-luc-hoc-duong'],
        ['SV11', 'Tài khoản (huy hiệu học tập)', 'sv/tai-khoan.php'],
        ['SV21', 'Tài liệu, tìm kiếm & tài liệu đã lưu', 'sv/tai-lieu.php'],
    ],
    'Học sinh – Gửi phiếu' => [
        ['SV12–16', 'Gửi phiếu 5 bước', 'sv/gui-phieu.php?buoc=1'],
        ['SV18', 'Ngoài giờ trực', 'sv/ngoai-gio-truc.php'],
        ['SV19', 'Phiếu của tôi', 'sv/phieu.php'],
        ['SV20', 'Chi tiết phiếu & hộp thư', 'sv/chi-tiet-phieu.php?id=KN-2026-0001'],
    ],
    'Cán bộ quản trị (cần đăng nhập cán bộ)' => [
        ['QT01', 'Đăng nhập cán bộ', 'qt/dang-nhap.php'],
        ['QT02', 'Danh sách phiếu', 'qt/phieu.php'],
        ['QT03', 'Xử lý phiếu', 'qt/xu-ly-phieu.php?id=KN-2026-0001'],
        ['QT04', 'Form mở danh tính', 'qt/xu-ly-phieu.php?id=KN-2026-0001&mo-danh-tinh=1'],
        ['QT05', 'Học liệu – quản lý chủ đề & câu hỏi', 'qt/hoc-lieu.php'],
        ['QT06', 'Soạn tình huống – thêm câu hỏi & đáp án', 'qt/soan-tinh-huong.php?chu-de=bao-luc-hoc-duong'],
        ['QT07', 'Cấu hình đầu mối (Quản trị viên)', 'qt/cau-hinh.php'],
        ['QT08', 'Tài khoản (Quản trị viên)', 'qt/tai-khoan.php'],
        ['QT09', 'Nhật ký thao tác', 'qt/nhat-ky.php'],
        ['QT10', 'Mã tham gia (Quản trị viên)', 'qt/ma-tham-gia.php'],
        ['QT11', 'Hỏi đáp & câu trả lời mở (Giáo viên)', 'qt/hoi-dap.php'],
        ['QT12', 'Email hệ thống đã gửi', 'qt/nhat-ky.php?tab=email'],
    ],
];

page_head('Mục lục màn hình');
?>
<div class="stack gap-20" style="max-width:880px;margin:0 auto;padding:40px 20px">
  <h1 class="t-h1">Mục lục màn hình</h1>
  <div class="demo-box">
    Mật khẩu mọi tài khoản mẫu: <code><?= e(DEMO_PASSWORD) ?></code> ·
    Học sinh <code>E2400249.test@example.com</code> · Quản trị viên <code>admin@abc-trung học.edu.vn</code> ·
    Người tiếp nhận độc lập <code>tungvt@abc-trung học.edu.vn</code> · Phụ trách công tác học sinh <code>baotq@abc-trung học.edu.vn</code> · Mã tham gia <code>HS2026</code>
  </div>
  <?php foreach ($groups as $title => $items): ?>
    <section class="card card--admin">
      <h2 class="t-h2"><?= e($title) ?></h2>
      <ul class="stack gap-8" style="margin:0;padding:0;list-style:none">
        <?php foreach ($items as [$code, $name, $href]): ?>
          <li class="row gap-12"><span class="badge badge--info badge--square" style="min-width:80px;text-align:center"><?= e($code) ?></span><a href="<?= e(url($href)) ?>"><?= e($name) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </section>
  <?php endforeach; ?>
</div>
<?php page_foot();
