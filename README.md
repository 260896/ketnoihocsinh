# KẾT NỐI HỌC SINH – “Lắng nghe – Đồng hành – Chia sẻ”

Không gian để học sinh tìm hiểu kiến thức, làm tình huống học tập và gửi phản ánh khi gặp vấn đề
trong học tập, sinh hoạt tại trường. Tên miền chạy thử: `ketnoihocsinh.local`.

> **Bản chạy thử:** toàn bộ tên trường, nhân sự, email, số điện thoại, tài khoản là **giả lập**.

Website viết bằng **HTML5 + CSS3 + PHP thuần** (không framework, không JavaScript).
Dữ liệu lưu dạng tệp JSON trong `data/`, không cần cài cơ sở dữ liệu.

## Chạy thử

Cách 1 – máy chủ PHP tích hợp (PHP ≥ 8.1, ví dụ PHP của XAMPP):

```bash
A:\xampp\php\php.exe -S localhost:8000 router.php
```

Mở http://localhost:8000/man-hinh.php để xem mục lục mọi màn hình.

Cách 2 – XAMPP/Apache: chép thư mục vào `htdocs/` và mở `http://localhost/<tên-thư-mục>/`.
Tệp `.htaccess` đã chặn truy cập trực tiếp `data/` và `includes/`.

**Tài khoản chạy thử** – mật khẩu chung `TestHS2026!`:

| Tên đăng nhập | Người | Vai trò | Xem danh tính / tệp gốc |
|---|---|---|---|
| `E2400249.test@example.com` | E2400249 | Học sinh | – |
| `admin@abc-trung học.edu.vn` | Admin (Nguyễn Minh Anh) | Quản trị chính | Có |
| `baotq@abc-trung học.edu.vn` | Trần Quốc Bảo | Công tác học sinh | Không |
| `hatl@abc-trung học.edu.vn` | Lê Thu Hà | Tư vấn, hỗ trợ học sinh | Không |
| `namph@abc-trung học.edu.vn` | Phạm Hoàng Nam | Hỗ trợ dự phòng | Không |
| `tungvt@abc-trung học.edu.vn` | Võ Thanh Tùng | Tiếp nhận độc lập | Có |

Mã tham gia để đăng ký: `HS2026` · Phản ánh mẫu: `KN-2026-0001` · Số hỗ trợ ngoài giờ: `1900 0000`.

**Phân công tự động** (sửa ở trang Cấu hình): Bạo lực/bắt nạt → Bảo (dự phòng Nam) · Thuốc lá điện tử → Bảo ·
Mạng xã hội → Hà · Quấy rối, xâm hại → Hà (dự phòng độc lập Tùng) · Góp ý → Bảo ·
Liên quan đến người đang xử lý → Tùng · Chưa rõ → hàng chờ phân loại.
Giờ tiếp nhận 08:00–17:00, thứ Hai–thứ Sáu; khẩn: xác nhận trong 15 phút; thường: phản hồi lần đầu trong 2 ngày làm việc.
Tệp: PDF, JPG, JPEG, PNG, MP4 · tối đa 10 MB/tệp, 5 tệp/phản ánh.

## Thêm câu hỏi & đáp án

1. Đăng nhập cán bộ → menu **Học liệu** (`qt/hoc-lieu.php`).
2. Chọn chủ đề (hoặc **+ Chủ đề mới**) → **Thêm tình huống**.
3. Trong trang **Soạn tình huống** (`qt/soan-tinh-huong.php`):
   - Nhập tiêu đề, câu hỏi, hình/video minh họa (tùy chọn).
   - Mỗi **lựa chọn** gồm nội dung + **giải thích** riêng; đánh dấu một lựa chọn là *Cách ứng xử phù hợp nhất*.
   - **Thêm lựa chọn** / nút ✕ để thêm hoặc bớt (2–6 lựa chọn).
   - **Lưu nháp** (học sinh chưa thấy) hoặc **Công bố**.
4. Ở danh sách: sửa, xem trước, công bố/gỡ, đổi thứ tự (↑ ↓), xóa.

## Cấu trúc

```
index.php              Landing page
man-hinh.php           Mục lục màn hình
tep.php                Trả tệp đã tải lên (kiểm quyền, ghi nhật ký khi cán bộ xem bản gốc)
dang-xuat.php          Đăng xuất
router.php             Router cho `php -S` (chặn data/, includes/)
sv/                    20 màn học sinh (SV01–SV20)
qt/                    9 màn cán bộ (QT01–QT09)
includes/
  config.php           Tên website, khẩu hiệu, trường, mật khẩu chạy thử, hằng số
  bootstrap.php        Nạp ở đầu mọi trang: session, CSRF, các tệp dưới đây
  helpers.php          e(), url(), redirect(), flash, tải tệp, giờ trực…
  store.php            Đọc/ghi dữ liệu (JSON) – thay bằng MySQL chỉ cần sửa tệp này
  seed.php             Dữ liệu giả lập ban đầu: cán bộ, phân công, 5 chủ đề học, phản ánh mẫu
  auth.php             Đăng nhập, phân quyền
  ui.php, icons.php    Thành phần giao diện dùng lại (ô nhập, nhãn, icon…)
  layout.php           Khung trang: mobile (học sinh) và quản trị (sidebar)
assets/css/            app.css (token màu + thành phần), landing.css
data/                  *.json + uploads/ (tạo tự động, không công khai)
```

Muốn làm lại dữ liệu từ đầu: xóa các tệp `data/*.json`.

## Ghi chú

- Mọi form POST có mã CSRF; mật khẩu băm bằng `password_hash`.
- Chưa gửi email thật: trang xác thực / quên mật khẩu hiện "liên kết chạy thử".
- Ảnh đính kèm phiếu (JPEG/PNG) được lưu 2 bản: **bản gốc** và **bản đã xóa siêu dữ liệu**
  (EXIF/GPS, máy chụp, thời điểm chụp, ghi chú – xem `strip_metadata()` trong `includes/helpers.php`).
  Học sinh và cán bộ mặc định chỉ thấy bản đã xóa; bản gốc chỉ Quản trị viên / người được chỉ định
  mở được qua nút "Xem bản gốc" và luôn ghi nhật ký. PDF, MP4 chưa xóa được siêu dữ liệu.
- Phiên bản React cũ nằm trong `_old-react/` (có thể xóa).
