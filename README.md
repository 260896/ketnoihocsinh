# KẾT NỐI HỌC SINH – “Lắng nghe – Đồng hành – Chia sẻ”

Không gian để học sinh tìm hiểu kiến thức, làm tình huống học tập và gửi phản ánh khi gặp vấn đề
trong học tập, sinh hoạt tại trường. Tên miền chạy thử: `ketnoihocsinh.local`.

> **Bản chạy thử:** toàn bộ tên trường, nhân sự, email, số điện thoại, tài khoản là **giả lập**.

Website viết bằng **HTML5 + CSS3 + PHP thuần** (PHP ≥ 8.1, không framework, không cơ sở dữ liệu).
Dữ liệu lưu dạng tệp JSON trong `data/`. JavaScript chỉ dùng để kiểm tra tệp đính kèm trước khi tải lên
(`assets/js/app.js`); máy chủ luôn kiểm tra lại.

Việc còn lại cho PM / nội dung / nhà trường và các bước đưa lên máy chủ thật: xem **[HUONG-DAN.md](HUONG-DAN.md)**.

## Chạy thử

Cách 1 – máy chủ PHP tích hợp:

```bash
A:\xampp\php\php.exe -S localhost:8000 router.php
```

Mở http://localhost:8000/man-hinh.php để xem mục lục mọi màn hình.

Cách 2 – XAMPP/Apache: mở `http://localhost/project/tieng-anh/`. `.htaccess` chặn truy cập trực tiếp `data/`, `includes/`.

Nhắc hạn / chuyển người dự phòng chạy kèm mỗi lượt truy cập (tối đa 1 lần/phút). Bản chính thức chạy thêm
`php cron.php` mỗi phút (xem HUONG-DAN.md).

**Tài khoản chạy thử** – mật khẩu chung `TestHS2026!`:

| Tên đăng nhập | Người | Vai trò | Xem phiếu | Mở danh tính / tệp gốc |
|---|---|---|---|---|
| `E2400249.test@example.com` | E2400249 | Học sinh | Phiếu của mình | – |
| `admin@example.com` | NV01 Nguyễn Minh Anh | Quản trị viên | Mọi phiếu trừ phiếu mình có liên quan (người được chỉ định) | Có |
| `baotq@example.com` | NV02 Trần Quốc Bảo | Xử lý phiếu | Phiếu được giao cho mình | Không |
| `hatl@example.com` | NV03 Lê Thu Hà | Xử lý phiếu | Phiếu được giao cho mình | Không |
| `namph@example.com` | NV04 Phạm Hoàng Nam | Xử lý phiếu (dự phòng) | Chỉ phiếu đã được giao | Không |
| `tungvt@example.com` | NV05 Võ Thanh Tùng | Xử lý phiếu (tiếp nhận độc lập) | Mọi phiếu trừ phiếu mình có liên quan + hàng chờ | Có |
| `giaovien@example.com` | Giáo viên chạy thử | Quản lý học liệu | Không | Không |

Email nguồn `…@abc-trung học.edu.vn` có dấu cách nên không dùng đăng nhập được; giữ ở trường `source_email` để đối chiếu (bộ hồ sơ mục 1, 8).
Mật khẩu chạy thử **không** hiển thị trên giao diện.

**Đăng ký học sinh:** nhập đúng cặp **mã tham gia + email** đã được nạp ở trang *Mã tham gia* (QT10).
Cặp chạy thử: `DEMO-0001`…`DEMO-0005` với `demo1@example.com`…`demo5@example.com`. Mỗi cặp dùng 1 lần.

Phản ánh mẫu: `KN-2026-0001`…`KN-2026-0012` theo bộ hồ sơ mục 8 (gồm ca xung đột lợi ích, người vắng mặt, ngoài giờ, khẩn).
Số hỗ trợ `1900 0000` là **số giả lập**: không có nút gọi (`phone_click = false`).

**Nguồn dữ liệu** (không đưa lên Git): Brief kỹ thuật + học liệu gốc trong `Khởi nghiệp_Tân Hưng…/`, bộ hồ sơ chạy thử trong `Ho_so_Ket_noi_hoc_sinh…/`.
Nhập lại: `php tools/nhap-ho-so.php <thư mục hồ sơ>` và `php tools/nhap-hoc-lieu.php <thư mục văn bản .txt> <thư mục Khởi nghiệp>` → `includes/du-lieu-mau.json` + `data/uploads/` (xem HUONG-DAN.md mục 4).

## Vai trò

| Vai trò | Làm được | Không được |
|---|---|---|
| Học sinh | Học, làm tình huống, hỏi đáp, gửi / theo dõi **phiếu của mình** | Xem phiếu người khác |
| Quản lý học liệu (giáo viên) | Soạn học liệu Nháp → Công bố, trả lời hỏi đáp, đọc "Vì sao em chọn" | Xem phiếu |
| Xử lý phiếu | Chỉ phiếu đang được giao cho mình (người dự phòng chỉ thấy sau khi được giao) | Xem danh tính phiếu ẩn danh, tệp gốc, phiếu mình có liên quan |
| Người được chỉ định (tối đa 2: NV01, NV05) | Mọi phiếu trừ phiếu mình có liên quan + mở danh tính (căn cứ, lý do, nhật ký) + tệp gốc + đánh dấu xung đột lợi ích | Phiếu mình có liên quan |
| Quản trị viên | Cấu hình, tài khoản, mã tham gia, nhật ký | Đọc phiếu (trừ khi là người được chỉ định) |

## Thêm câu hỏi & đáp án

1. Đăng nhập tài khoản giáo viên → menu **Học liệu** (`qt/hoc-lieu.php`).
2. Chọn chủ đề (hoặc **+ Chủ đề mới**) → **Thêm tình huống**.
3. Trong **Soạn tình huống** (`qt/soan-tinh-huong.php`): tiêu đề, câu hỏi, hình/video (tùy chọn),
   2–6 lựa chọn mỗi lựa chọn có **giải thích** riêng, đánh dấu *phù hợp nhất* → **Lưu nháp** hoặc **Công bố**.
4. Ở danh sách: sửa, xem trước, công bố/gỡ, đổi thứ tự, xóa.

## Cấu trúc

```
index.php              Landing page (3 lối vào: Khám phá tình huống / Em cần hỗ trợ / Gửi góp ý hoặc phản ánh)
trang.php              Chính sách bảo mật, Quy tắc sử dụng, Cơ chế ẩn danh, Giờ tiếp nhận (?p=…)
man-hinh.php           Mục lục màn hình
tep.php                Trả tệp đã tải lên (kiểm quyền theo phiếu, ghi nhật ký khi tải bản gốc)
cron.php               Nhắc hạn, chuyển người dự phòng – chạy bằng cron / Task Scheduler
router.php             Router cho `php -S` (chặn data/, includes/)
sv/                    Màn học sinh (SV01–SV21)
qt/                    Màn cán bộ (QT01–QT12)
includes/
  config.php           Tên website, vai trò, mức ưu tiên, giới hạn chống dò, hằng số
  bootstrap.php        Nạp ở đầu mọi trang: session, CSRF, nhắc hạn, các tệp dưới đây
  helpers.php          e(), url(), redirect(), flash, tải tệp (kiểm MIME thực), xóa siêu dữ liệu ảnh…
  store.php            Đọc/ghi dữ liệu JSON, phiếu, email (outbox), SLA, hỏi đáp, huy hiệu
  seed.php             Dữ liệu giả lập ban đầu
  auth.php             Đăng nhập, chống dò, vai trò & quyền xem phiếu
  ui.php, icons.php    Thành phần giao diện dùng lại
  layout.php           Khung trang: mobile (học sinh) và quản trị (menu theo vai trò)
assets/css/            app.css (màu #1565C0 / #C62828 + thành phần), landing.css
assets/js/app.js       Kiểm tra loại / dung lượng / số tệp trước khi tải lên
data/                  *.json + uploads/ (tạo tự động, không công khai, không đưa lên Git)
```

### Tệp dữ liệu

| Tệp | Nội dung |
|---|---|
| `tickets.json` | Nội dung phiếu – **không** chứa id/tên/email người gửi |
| `ticket_owners.json` | Liên kết mã phiếu → tài khoản, lưu tách riêng (CLAUDE.md 4.1) |
| `codes.json` | Cặp mã tham gia + email do nhà trường nạp |
| `outbox.json` | Mọi email hệ thống gửi (không chứa nội dung phiếu) |
| `questions.json` | Hỏi đáp và câu trả lời "Vì sao em chọn" |
| `audit.json` | Nhật ký thao tác |
| `throttle.json`, `sla.json` | Chống dò mật khẩu / mã; mốc chạy nhắc hạn |

Muốn làm lại dữ liệu từ đầu: xóa các tệp `data/*.json`.

## Ghi chú kỹ thuật

- Mọi form POST có mã CSRF; mật khẩu băm bằng `password_hash`; mọi dữ liệu in ra đều qua `e()`.
- Sai mật khẩu / mã tham gia 5 lần với một email → khóa 15 phút; 30 lần từ một IP → khóa IP (trường dùng chung IP).
- Tệp đính kèm: kiểm tra đuôi, dung lượng (10 MB = 10.000.000 byte), tệp rỗng, số tệp và **MIME thực** (`finfo`) ở máy chủ; JS kiểm tra trước ở trình duyệt. Học liệu (video) tối đa 50 MB.
- Hạn: phiếu khẩn xác nhận trong 15 phút tính trong giờ làm việc (nhận ngoài giờ → từ đầu giờ làm việc kế tiếp); phiếu thường 2 ngày làm việc. Người phụ trách **vắng mặt** → phiếu mới chuyển người dự phòng. **Xung đột lợi ích** → người liên quan bị loại khỏi phiếu, chuyển người độc lập.
- Ảnh JPEG/PNG lưu 2 bản: gốc và đã xóa siêu dữ liệu (EXIF/GPS, máy chụp, thời điểm chụp). Cán bộ xem bản đã xóa;
  bản gốc chỉ người được chỉ định mở, luôn ghi nhật ký. PDF, MP4 **chưa** xóa được siêu dữ liệu.
- Email: `mail_enabled = false` (mặc định) thì thư chỉ lưu ở *Nhật ký → Email đi*. Bản chạy thử (`is_demo`)
  hiện liên kết xác thực / đặt lại mật khẩu học sinh ngay trên trang; **không bao giờ** hiện với tài khoản cán bộ.
- Phiên bản React cũ nằm trong `_old-react/` (có thể xóa).
