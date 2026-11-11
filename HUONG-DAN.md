# Hướng dẫn hoàn thành dự án KẾT NỐI HỌC SINH

Cập nhật: 11/11/2026. Đối chiếu với bảng *Task list dự án Kết nối học sinh* (44 việc).

Phần code của Dev đã làm xong và đã kiểm thử: `tests/e2e.sh` chạy 71/71 bước đạt, bám theo checklist CLAUDE.md mục 12.
Tài liệu này dành cho phần còn lại: những việc cần **nhà trường quyết định**, **PM duyệt**, **nhóm nội dung soạn**,
và các bước **đưa lên máy chủ thật**.

Một số chỗ trong code đang dùng **giả định** để hệ thống chạy được. Mỗi giả định ghi rõ ở đâu và sửa bằng cách nào.
Không cần sửa code cho các việc này, trừ khi có ghi khác.

---

## 1. Tình trạng 44 việc

| # | Việc | Tình trạng | Còn phải làm | Ai |
|---|---|---|---|---|
| 1 | Chốt cách cấp tài khoản | Đã làm: cặp mã + email nạp sẵn | Nhà trường xác nhận cách này | Nhà trường |
| 2 | Đầu mối nhóm Đào tạo, CSVC, tiếp nhận chung | Đã thêm 2 nhóm, phiếu vào hàng chờ | Chỉ định người (mục 2.2) | Nhà trường |
| 3 | Dự phòng nhóm Thuốc lá, MXH, Góp ý | Để trống đúng như nguồn; quá hạn thì nhắc quản trị viên | Chỉ định người | Nhà trường |
| 4 | Quy tắc mở danh tính, báo gia đình/cơ quan | Đã có bản nháp | Duyệt văn bản (mục 2.3) | PM + Nhà trường |
| 5 | Chốt công nghệ | Xong: PHP thuần + JSON, ghi trong CLAUDE.md mục 2 | – | – |
| 6 | Sửa email, mật khẩu thử | Xong (email thử `@example.com`, mật khẩu không hiện trên giao diện) | Đổi mật khẩu thật khi triển khai | PM |
| 7 | Kịch bản chatbot | Đã chạy trong code | Duyệt câu chữ (mục 3.1) | PM |
| 8 | Môi trường local, Git, CLAUDE.md | Đã có Git (chưa có remote) | Tên miền `ketnoihocsinh.local`, đẩy lên Git (mục 5.1) | Dev |
| 9 | Giao diện nền | Xong: đủ 3 màu, header, footer, 3 lối vào | Thử ở màn 360px trên máy thật (#38) | PM |
| 10 | Vai trò và phân quyền | Xong: thêm vai trò Giáo viên | – | – |
| 11 | Đăng ký, đăng nhập, đặt lại mật khẩu, khóa | Xong, có chống dò | – | – |
| 12 | Trang Chính sách, Ẩn danh, Quy tắc, Giờ tiếp nhận | Đã có bản nháp tại `trang.php` | Duyệt câu chữ (mục 3.2) | PM |
| 13–14 | Module học, quản lý học liệu | Xong | – | – |
| 15–16 | Soạn nội dung chủ đề 1–3 | **Xong**: 3 câu/chủ đề từ bộ câu hỏi gốc, giải thích từng lựa chọn, ảnh, infographic, video, PDF, tài liệu thảo luận | – | – |
| 17 | Chủ đề 4, 5 | Đã nhập đủ như chủ đề 1–3 | Giáo viên duyệt mức phù hợp lứa tuổi | Nội dung + Nhà trường |
| 18 | Nhập và công bố 5 chủ đề | Xong | – | – |
| 19 | Hỏi đáp, "Vì sao em chọn" | Xong (QT11) | – | – |
| 20 | Huy hiệu học tập | Xong (chỉ gắn với việc học) | – | – |
| 21–28, 30, 32 | Kênh phản ánh, quản trị | Xong | – | – |
| 22 | Tách bảng danh tính | Xong (`ticket_owners.json`) | – | – |
| 23 | Mở danh tính tối đa 2 người | Xong | – | – |
| 24 | Tệp đính kèm | Xong: kiểm cả client, server, MIME thực | PDF/MP4 chưa xóa được siêu dữ liệu (mục 5.5) | – |
| 29 | Mẫu trả lời mỗi nhóm | Đã có bản nháp 8 mẫu | Duyệt câu chữ (mục 3.3) | PM |
| 31 | Email, nhắc hạn, chuyển dự phòng | Xong (đang lưu ở hộp thư đi) | Bật gửi thư thật (mục 5.3) | Dev |
| 33 | Cron thật | Có `cron.php` | Đặt lịch trên máy chủ (mục 5.2) | Dev |
| 34 | Gợi ý phiếu nghi trùng | Xong (chỉ gợi ý) | – | – |
| 35 | Dữ liệu thử | Xong: 12 phiếu theo bộ hồ sơ mục 8 (xung đột lợi ích, vắng mặt, ngoài giờ, khẩn) + 5 bản ghi danh tính tách riêng | – | – |
| 36 | Chạy 10 luồng kiểm tra | Xong bằng tự động (71 bước) | PM thử tay theo mục 6 | PM |
| 37 | Rà bảo mật | Xong, không còn lỗi mức cao | Rà lại trước khi chạy thật | Dev |
| 38 | Thử trên điện thoại thật | Chưa | Mục 6.2 | PM |
| 39 | Sửa lỗi sau kiểm thử | Chờ kết quả #38 | Chạy lại `tests/e2e.sh` | Dev |
| 40–43 | Sơ đồ, ảnh chụp, kịch bản demo, bản mô tả | Chưa | Mục 7 | PM |
| 44 | Pilot 1–2 lớp | Chưa | Mục 8 | PM + Nhà trường |

---

## 2. Việc của nhà trường

### 2.1 Xác nhận cách cấp tài khoản (#1)
Hiện tại nhà trường **nạp sẵn cặp mã tham gia + email** cho từng học sinh. Học sinh tự đăng ký bằng đúng cặp đó,
và mỗi cặp chỉ dùng được một lần.

- **Điều cần biết:** nhà trường sẽ biết email nào là của học sinh nào. Phiếu ẩn danh vẫn ẩn tên và email với cán bộ xử lý.
- **Cách nạp:** đăng nhập quản trị → **Mã tham gia** → dán danh sách, mỗi dòng `mã,email`. Nếu chỉ có `email` thì hệ thống tự sinh mã.
  Có thể tải tệp `.csv` thay cho việc dán. Sau đó bấm **Tải danh sách (CSV)** để lấy mã gửi cho học sinh.
- Trước khi dùng thật, **xóa 5 cặp `DEMO-…`**.

### 2.2 Chỉ định đầu mối còn thiếu (#2, #3)
Đăng nhập quản trị → **Cấu hình đầu mối** → bảng *Đầu mối theo nhóm vấn đề*:

| Nhóm | Đang đặt | Cần nhà trường chọn |
|---|---|---|
| Chất lượng giảng dạy, đào tạo | Chưa có đầu mối → vào hàng chờ | Đầu mối chính |
| Cơ sở vật chất | Chưa có đầu mối → vào hàng chờ | Đầu mối chính |
| Chưa rõ / Khác | Hàng chờ, Nam và Tùng xem | Người tiếp nhận chung (nếu muốn) |
| Thuốc lá, Mạng xã hội, Góp ý | Chưa có dự phòng (đúng nguồn) | Chỉ định người dự phòng |

Danh sách chỉ hiện cán bộ có vai trò **Xử lý phiếu**. Muốn thêm người, vào **Tài khoản** để tạo trước.

### 2.3 Duyệt quy tắc mở danh tính (#4)
Bản nháp nằm ở 2 chỗ. Duyệt xong, gửi câu chữ cuối cho Dev để thay:

1. `trang.php?p=an-danh`, mục *Khi nào thông tin được chuyển đi*: báo gia đình hoặc cơ quan chức năng khi có nguy cơ gây hại,
   hoặc theo yêu cầu cơ quan có thẩm quyền; báo trước cho học sinh, trừ khi việc báo trước làm em gặp nguy hiểm hơn.
2. Bước 5 gửi phiếu (`sv/gui-phieu.php`): cùng nội dung, viết ngắn.

Danh sách **căn cứ mở danh tính** trong form QT04 (`qt/xu-ly-phieu.php`, biến `$basisOptions`) cũng cần nhà trường duyệt.

Chỉ **2 người** được mở danh tính (Nguyễn Minh Anh, Võ Thanh Tùng). Hệ thống không cho cấp quyền này cho người thứ 3.

### 2.4 Duyệt nội dung chủ đề 4, 5 (#17)
Giáo viên đọc chủ đề *Phòng, chống tảo hôn* và *Phòng, chống quấy rối tình dục*, sau đó ghi xác nhận
"phù hợp lứa tuổi" vào bảng task.

---

## 3. Việc PM duyệt câu chữ

Mọi câu chữ dưới đây đều đã có trong hệ thống. PM chỉ cần đọc trên giao diện và gửi bản sửa cho Dev.

### 3.1 Kịch bản gửi phiếu (#7)
Mở `sv/gui-phieu.php?buoc=1` bằng tài khoản học sinh, đi hết 5 bước:
- Bước 1: 3 loại yêu cầu và câu gợi ý (`REQUEST_TYPES` trong `includes/config.php`).
- Bước 2: danh sách nhóm (sửa ở trang Cấu hình).
- Bước 3: câu "Bạn có cần hỗ trợ ngay không?" và khung số khẩn cấp.
- Bước 5: khung "Ai xem được phiếu này?".
- Tin nhắn tự động sau khi gửi: `ticket_create()` trong `includes/store.php`.

Mục tiêu: gửi 1 phiếu không quá 6 thao tác. Bản hiện tại cần 6 thao tác:
chọn loại → chọn nhóm → có/không → mô tả → xem lại → xác nhận.

### 3.2 Trang chính sách (#12)
Đọc 4 trang tại `trang.php?p=chinh-sach`, `quy-tac`, `an-danh`, `gio-tiep-nhan`. Nội dung nằm trong mảng `$pages` ở đầu `trang.php`.
Quy tắc khi sửa: **không hứa "bảo mật tuyệt đối"**, và luôn nói rõ ai xem được gì.
Duyệt xong thì đặt `'is_demo' => false` để bỏ dòng "Bản nháp" (xem mục 5.4).

### 3.3 Mẫu trả lời (#29)
Quản trị → **Cấu hình đầu mối** → *Mẫu trả lời*. Hiện có 3 mẫu chung và 8 mẫu theo nhóm. Mỗi mẫu gắn với một nhóm qua ô *Dùng cho nhóm*.
Cán bộ chỉ thấy mẫu chung và mẫu của nhóm phiếu đang xử lý.

---

## 4. Việc nhóm nội dung (#17 và 3 chủ đề còn thiếu)

Đã nhập từ thư mục **`nguon/`** (xem `nguon/README.md`): bộ câu hỏi gốc (15 câu), tài liệu thảo luận 5 chủ đề (nội dung học tập: nhận diện, cơ sở pháp lý, nơi tiếp nhận, hiểu nhầm, tình huống), ảnh minh họa, infographic, video, PDF.
Khi nhà trường sửa tài liệu nguồn, nhập lại như sau (trên máy có PowerShell):

1. Thay tệp trong `nguon/` (giữ đúng tên). Nếu sửa `.docx`, lưu lại bản `.txt` cùng tên bên cạnh (PHP của XAMPP không có ZipArchive) – bằng Word: *Lưu thành → Văn bản thuần (.txt), mã hóa UTF-8*.
2. `php tools/nhap-hoc-lieu.php` → đọc `nguon/hoc-lieu/`, ghi `includes/du-lieu-mau.json` (câu hỏi, nội dung, tài liệu, danh mục tham khảo).
3. `php tools/nhap-ho-so.php` → đọc `nguon/ho-so/`: nhân sự, 12 phiếu mẫu, danh tính giả lập.
   Media (ảnh, infographic, video, PDF) được website tự chép từ `nguon/hoc-lieu/<id>/` vào `data/uploads/`.
4. Sao lưu rồi xóa `data/*.json` (và `data/uploads/{images,docs}/hoc-lieu/` nếu thay media) để website tạo lại dữ liệu mẫu.

Thêm tình huống mới trên giao diện:

1. Đăng nhập `giaovien@example.com` → **Học liệu** → chọn chủ đề → **Thêm tình huống**.
2. Nhập tiêu đề, câu hỏi, hình (JPG/PNG/WebP, tối đa 10 MB) hoặc link video.
3. Mỗi lựa chọn (2–6 lựa chọn) có nội dung và **giải thích riêng**. Đánh dấu một lựa chọn *phù hợp nhất*.
4. **Lưu nháp** → bấm *Xem trước* để kiểm tra → **Công bố**. Học sinh chỉ thấy bản đã công bố.
5. Tài liệu kèm theo (PDF, ảnh, link video): ở mục *Tài liệu* của chủ đề.

Theo Brief còn thiếu 3 chủ đề: *Tin sai sự thật*, *Tiêu cực trong trường*, *Chất lượng đào tạo*. Tạo bằng **+ Chủ đề mới**.

Lưu ý với chủ đề 4, 5: viết phù hợp lứa tuổi, không mô tả chi tiết hành vi, luôn có hướng dẫn tìm người lớn tin cậy và các số 111, 113.

---

## 5. Đưa lên máy chủ thật (Dev)

### 5.1 Tên miền local và Git (#8)
Hai việc này cần quyền quản trị Windows nên chưa làm tự động.

1. Mở Notepad bằng quyền Administrator, thêm vào `C:\Windows\System32\drivers\etc\hosts`:
   `127.0.0.1  ketnoihocsinh.local`
2. Thêm vào `A:\xampp\apache\conf\extra\httpd-vhosts.conf`:
   ```apache
   <VirtualHost *:80>
       ServerName ketnoihocsinh.local
       DocumentRoot "A:/xampp/htdocs/project/tieng-anh"
       <Directory "A:/xampp/htdocs/project/tieng-anh">
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```
   Sau đó khởi động lại Apache và mở `http://ketnoihocsinh.local/`.
3. Git đã khởi tạo trong thư mục dự án. Tạo repo trên GitHub/GitLab rồi chạy:
   `git remote add origin <url>` và `git push -u origin master`.
   `data/*.json` và `data/uploads/` không được đưa lên Git.

### 5.2 Cron nhắc hạn (#33)
Hệ thống có tự chạy kèm mỗi lượt truy cập, nhưng **khi không ai truy cập thì nhắc hạn không chạy**.
Vì vậy cần đặt lịch chạy mỗi phút:
- **Windows (Task Scheduler):** tạo tác vụ chạy `A:\xampp\php\php.exe A:\xampp\htdocs\project\tieng-anh\cron.php`,
  lặp lại mỗi 1 phút, chạy cả khi chưa đăng nhập.
- **Linux:** `* * * * * php /đường/dẫn/cron.php >> /var/log/ketnoihocsinh-cron.log 2>&1`

Kiểm tra: tạo phiếu "cần hỗ trợ ngay" và đừng bấm xác nhận. Sau 15 phút, phiếu chuyển cho người dự phòng,
và *Nhật ký* có dòng "Chuyển dự phòng".

### 5.3 Bật gửi email thật (#31)
1. Cấu hình PHP gửi được thư (SMTP của nhà trường), hoặc thay `mail_send()` trong `includes/store.php` bằng thư viện SMTP.
2. Trong `data/config.json`, đặt `"mail_enabled": true`.
3. Kiểm tra ở **Nhật ký → Email đi**: cột *Đã gửi* phải là "Có".

Thư **không bao giờ** chứa nội dung phiếu (CLAUDE.md 4.5). Khi sửa `notify_*()`, giữ nguyên quy tắc này.

### 5.4 Tắt chế độ chạy thử
Trong `includes/config.php`, đặt `'is_demo' => false`. Thay đổi này sẽ:
- ẩn mật khẩu chạy thử trên trang đăng nhập;
- ẩn liên kết xác thực và đặt lại mật khẩu trên trang (khi đó chỉ gửi qua email);
- che mã bí mật trong *Email đi*;
- bỏ dòng "Bản chạy thử" và "Bản nháp".

**Chặn lập chỉ mục:** bản demo đặt `'noindex' => true`, nên mọi trang có `<meta name="robots" content="noindex, nofollow">`
và header `X-Robots-Tag` (cả tệp tĩnh, nhờ `.htaccess`). Nhờ vậy Google không đưa bản demo lên kết quả tìm kiếm, tránh bị nhầm là kênh chính thức.
Chỉ đặt `false` và xóa khối `X-Robots-Tag` trong `.htaccess` khi nhà trường đưa vào dùng thật.
**Không** chặn bằng `robots.txt` (`Disallow`): làm vậy Google không đọc được thẻ noindex, nên link vẫn có thể hiện trên kết quả tìm kiếm.

Đổi luôn `DEMO_PASSWORD` và mật khẩu của mọi tài khoản cán bộ. Cách đổi: tạo tài khoản mới ở trang **Tài khoản**,
hệ thống gửi email đặt mật khẩu lần đầu.

### 5.5 Giới hạn còn lại cần biết
- **PDF và MP4 chưa xóa được siêu dữ liệu.** Cán bộ phải mở bằng nút "Xem bản gốc (ghi nhật ký)", và chỉ người được chỉ định mới mở được.
  Muốn xóa thì cần công cụ ngoài như `exiftool` hoặc `ffmpeg -map_metadata -1` trên máy chủ.
- Dữ liệu lưu bằng tệp JSON, phù hợp quy mô 1 trường. Nếu nhiều người dùng đồng thời, nên chuyển sang MySQL:
  chỉ cần viết lại `includes/store.php`.
- Nên dùng **HTTPS** khi chạy thật, và sao lưu `data/` mỗi ngày.

---

## 6. Kiểm thử (#36, #38, #39)

### 6.1 Tự động
```bash
PHP=/a/xampp/php/php.exe bash tests/e2e.sh
```
Bộ test chạy 71 bước theo CLAUDE.md mục 12: đăng ký, học, gửi phiếu, tệp, ẩn danh, mở danh tính, định tuyến, SLA, phân quyền, XSS, CSRF.
Script tự sao lưu và khôi phục `data/`. Kết quả phải là `0 lỗi`. Chạy lại sau mỗi lần sửa (#39).

### 6.2 Thử tay trên điện thoại (#38)
Thử trên 1 máy Android và 1 iPhone. Mở `man-hinh.php` để có đường dẫn tới mọi màn hình. Kiểm tra:
- [ ] Màn 360px không bị tràn ngang, chữ đọc được, nút bấm đủ lớn.
- [ ] Học hết 1 chủ đề, đăng xuất rồi đăng nhập lại vẫn còn tiến độ.
- [ ] Gửi 1 phiếu có ảnh chụp từ camera. Cán bộ xem thấy ảnh, và ảnh đã xóa vị trí.
- [ ] Bấm "Gọi 1900 0000" thì mở được trình gọi điện.
- [ ] Ghi số thao tác để gửi 1 phiếu (mục tiêu ≤ 6).

---

## 7. Tài liệu trình bày (#40–#43)

- **#40 Sơ đồ chức năng + sitemap:** lấy từ `man-hinh.php` (mục lục 29 mục màn hình) và bảng vai trò trong README.md.
- **#41 Ảnh chụp 5 màn hình:** trang chủ (`/`), bài học (`sv/tinh-huong.php?chu-de=bao-luc-hoc-duong&so=1`),
  gửi phiếu (`sv/gui-phieu.php?buoc=3`), theo dõi phiếu (`sv/chi-tiet-phieu.php?id=KN-2026-0001`), hàng chờ cán bộ (`qt/phieu.php`).
- **#42 Kịch bản demo ~7 phút:**
  1. Học sinh `E2400249.test@example.com` gửi phiếu ẩn danh nhóm Bắt nạt, có ảnh (1 phút 30).
  2. Trần Quốc Bảo nhận phiếu, thấy nội dung, không thấy tên, dùng mẫu trả lời rồi gửi (1 phút 30).
  3. Học sinh thấy "Phiếu có cập nhật" và trả lời trong hộp thư (1 phút).
  4. Võ Thanh Tùng mở danh tính: chọn căn cứ, nhập lý do, rồi xem dòng ghi trong Nhật ký (1 phút 30).
  5. Gửi phiếu "cần hỗ trợ ngay" để thấy số 1900 0000 hiện ra. Mở *Nhật ký → Email đi* để thấy thư báo không chứa nội dung (1 phút 30).
  Trước khi demo, chạy `tests/e2e.sh` để chắc dữ liệu sạch. Script không để lại dữ liệu thử.
- **#43 Bản mô tả dự án:** cập nhật theo README.md và bảng ở mục 1 của tài liệu này.

---

## 8. Pilot 1–2 lớp (#44)

1. Nhà trường chốt các mục 2.1–2.4, sau đó Dev làm mục 5.2–5.4.
2. Nạp cặp mã + email cho học sinh 1–2 lớp, rồi in hoặc gửi mã cho từng em.
3. Hướng dẫn giáo viên: trang Học liệu và Hỏi đáp. Hướng dẫn cán bộ: trang Phiếu và mẫu trả lời.
4. Theo dõi 4 chỉ số trong 2–4 tuần, lấy từ *Nhật ký* (xuất CSV):
   - số thao tác để gửi 1 phiếu;
   - % phiếu phải phân loại lại (dòng "Phân loại");
   - số phiếu quá hạn (dòng "Nhắc hạn", "Chuyển dự phòng");
   - thời gian phản hồi lần đầu (từ "Phiếu được tạo" đến "Phản hồi").

Không dùng số lượng phản ánh làm thành tích thi đua.
