# Tài liệu nguồn của dự án

Thư mục này lưu **toàn bộ tài liệu gốc** nhà trường cung cấp. Trước đây các tài liệu nằm trong 2 thư mục tải từ Google Drive:
`Khởi nghiệp_Tân Hưng-20261111T092153Z-1-001` và `Ho_so_Ket_noi_hoc_sinh-20261111T092041Z-1-001`. Hai thư mục đó đã có thể xóa.
Mọi tệp đã được đối chiếu md5: nội dung **giống hệt bản gốc**, chỉ đổi tên sang không dấu (an toàn cho Apache và Git).

Thư mục bị chặn truy cập qua web (`.htaccess`, `router.php`). Website đọc học liệu qua `includes/du-lieu-mau.json`, và tự chép media
từ đây vào `data/uploads/` khi tạo dữ liệu (`seed_ensure_media()` trong `includes/seed.php`).

## Thứ tự ưu tiên khi các nguồn khác nhau

1. **Brief kỹ thuật** – nguyên tắc (ẩn danh, phân quyền, luồng nghiệp vụ, màu #1565C0 / #C62828).
2. **Thông tin giả lập chạy thử** – cấu hình (nhân sự, đầu mối, giờ trực, tệp, tài khoản thử).
3. **Bộ hồ sơ chạy thử** – đề xuất chi tiết, chỉ áp dụng khi không trái hai nguồn trên.

## Mục lục

| Tệp trong `nguon/` | Tệp gốc | Dùng ở đâu |
|---|---|---|
| `brief/brief-ky-thuat-giai-doan-1.pdf` | `0.nguồn chạy dev/Brief kỹ thuật giai đoạn 1 - Website giáo dục và tiếp nhận phản ánh.pdf` | CLAUDE.md, toàn bộ nghiệp vụ |
| `gia-lap/thong-tin-gia-lap-chay-thu.docx` (+ `.txt`) | `0.nguồn chạy dev/Thông tin giả lập chạy thử mục 4.docx` | `includes/seed.php` (cấu hình), CLAUDE.md mục 6, 7, 10 |
| `logo-schoolink.png` | `logo schoolink.png` | `assets/img/logo-schoolink.png` |
| `hoc-lieu/bo-cau-hoi-trac-nghiem.docx` (+ `.txt`) | `Bộ câu hỏi trắc nghiệm - Nội dung học tập ban đầu (1).docx` | 15 câu hỏi, đáp án, giải thích |
| `hoc-lieu/thao-luan/chu-de-N-<id>.docx` (+ `.txt`) | `thảo luận chủ đề/Chu_de_N_….docx` | Nội dung học tập từng chủ đề |
| `hoc-lieu/tai-lieu-tham-khao.docx` (+ `.txt`) | `tài liệu/Danh mục tài liệu tham khảo 5 chủ đề.docx` | Trang `trang.php?p=tham-khao` |
| `hoc-lieu/<id>/infographic.png` | `infographic/N. <tên chủ đề>.png` | Trang chủ đề |
| `hoc-lieu/<id>/video.mp4` | `video/Chủ đề N ….mp4` | Trang chủ đề |
| `hoc-lieu/<id>/tai-lieu.pdf` | `tài liệu/N. <tên chủ đề>.pdf` | Tài liệu của chủ đề |
| `hoc-lieu/<id>/anh-*.jpg` | `hình ảnh chủ đề/N. <tên chủ đề>/*.jfif` | Ảnh minh họa tình huống |
| `ho-so/*` (26 tệp) | `Ho_so_Ket_noi_hoc_sinh/*` (trùng với `Khởi nghiệp_Tân Hưng/Bộ hồ sơ/`) | Nhân sự, 12 phiếu mẫu, danh tính giả lập, quy tắc bổ sung |

`<id>`: `bao-luc-hoc-duong` (1), `thuoc-la-dien-tu` (2), `an-toan-mang-xa-hoi` (3), `phong-chong-tao-hon` (4), `phong-chong-quay-roi` (5).

Các tệp `.txt` là chữ trích từ `.docx` (PHP của XAMPP không có ZipArchive để đọc thẳng `.docx`).

## Cập nhật khi nhà trường sửa tài liệu

1. Thay tệp trong `nguon/` (giữ đúng tên). Nếu sửa `.docx`, lưu lại bản `.txt` cùng tên: Word → *Lưu thành* → *Văn bản thuần (.txt)*, mã hóa UTF-8.
2. `php tools/nhap-hoc-lieu.php` – học liệu, tài liệu, danh mục tham khảo → `includes/du-lieu-mau.json`.
3. `php tools/nhap-ho-so.php` – nhân sự, 12 phiếu mẫu, danh tính giả lập.
4. Sao lưu rồi xóa `data/*.json` và `data/uploads/{images,docs}/hoc-lieu/` để website tạo lại dữ liệu và media mẫu.

Thư mục này nặng khoảng 69 MB (chủ yếu là 5 video và 5 PDF). Mỗi tệp đều dưới giới hạn 100 MB của GitHub.
