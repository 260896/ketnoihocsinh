# CLAUDE.md – Dự án KẾT NỐI HỌC SINH

> Đọc file này trước khi sửa hoặc kiểm tra code. Nguồn gốc: `Brief Website.docx` (yêu cầu nghiệp vụ) và `Thông tin giả lập chạy thử` (dữ liệu cấu hình). Khi hai nguồn khác nhau, **dữ liệu giả lập thắng ở phần cấu hình**, **Brief thắng ở phần nguyên tắc**.

## 1. Dự án là gì

Website giáo dục ngoại khóa cho học sinh trung học, gồm hai phần gắn với nhau:

1. **Học qua tình huống**: học sinh chọn cách ứng xử, nhận giải thích, làm lại, có lưu tiến độ.
2. **Kênh phản ánh có kiểm soát**: học sinh gửi phản ánh / đề nghị hỗ trợ / góp ý (có danh tính hoặc ẩn danh), nhà trường tiếp nhận, phân loại, phản hồi qua mã phiếu.

Điểm cốt lõi là **tương tác hai chiều**, không phải trang đọc tin.

- Tên: **KẾT NỐI HỌC SINH**. Thông điệp: "Lắng nghe – Đồng hành – Chia sẻ"
- Logo: dùng tên chữ (chưa có logo)
- Tên miền tạm: `ketnoihocsinh.local`
- Phạm vi: 1 trường, bản chạy thử. Toàn bộ dữ liệu là **giả lập**.

## 2. Công nghệ và cấu trúc

> TODO: điền stack thực tế (WordPress + plugin `school-report-core` / HTML-JS tĩnh / framework khác), lệnh chạy, lệnh test, cấu trúc thư mục.
> Khi chưa điền: Claude tự xác định stack từ repo trước khi sửa, không tự đổi stack.

## 3. Vai trò và phân quyền

| Vai trò | Làm được | Không được |
|---|---|---|
| Học sinh | Học, làm tình huống, đặt câu hỏi, gửi phiếu, xem **chỉ phiếu của mình**, trả lời yêu cầu bổ sung | Xem phiếu người khác |
| Giáo viên / quản lý học liệu | Tải lên, nhập câu hỏi–đáp án–giải thích, Nháp → Công bố, trả lời hỏi đáp, xem câu trả lời mở | Xem phiếu phản ánh |
| Cán bộ xử lý phiếu | Xem phiếu nhóm được giao, sửa phân loại, nâng ưu tiên, trả lời, dùng mẫu | **Xem danh tính phiếu ẩn danh** |
| Người được mở danh tính | Như cán bộ + mở danh tính (bắt nhập lý do, ghi nhật ký) | – |
| Admin kỹ thuật | Cấu hình hệ thống, khóa tài khoản | Mặc định không đọc nội dung phiếu |

## 4. Quy tắc KHÔNG ĐƯỢC LÀM SAI (bảo mật, ẩn danh)

Mọi thay đổi code phải giữ đúng các điều sau. Nếu phát hiện vi phạm, báo ngay, ưu tiên hơn mọi lỗi khác.

1. **Tách dữ liệu**: nội dung phiếu và liên kết `phiếu ↔ tài khoản` lưu tách riêng. Màn hình / API của cán bộ xử lý không được trả về tên, email, mã HS, lớp của phiếu ẩn danh (kiểm tra cả JSON response, HTML ẩn, log, email).
2. **Chỉ 2 người mở được danh tính**: Nguyễn Minh Anh (admin) và Võ Thanh Tùng (tiếp nhận độc lập). Mỗi lần mở: bắt nhập lý do + ghi nhật ký (ai, lúc nào, phiếu nào, lý do).
3. Ẩn danh là **ẩn ở lớp hiển thị và phân quyền**, không phải ẩn tuyệt đối. Giao diện không được hứa "bảo mật tuyệt đối". Trước khi gửi phải hiển thị: ai xem được nội dung, ai mở được danh tính, khi nào thông tin được chuyển cho người có trách nhiệm.
4. **Tệp đính kèm**: bản phía học sinh tải về đã xóa metadata (GPS, thiết bị, thời điểm chụp). Bản gốc chỉ cán bộ có thẩm quyền xem, mỗi lần tải bản gốc ghi nhật ký.
5. **Email / thông báo ngoài hệ thống** chỉ ghi "Phiếu có cập nhật", không chứa nội dung phiếu.
6. Xác nhận tự động sau khi gửi phải ghi rõ "tự động", không giống phản hồi của cán bộ.
7. Không có trang công khai nào hiển thị phiếu, danh sách vụ việc hay dữ liệu cá nhân.
8. Hệ thống không tự gắn nhãn ai là người vi phạm. Phiếu nghi trùng chỉ gợi ý liên kết, không tự xóa / đóng.
9. Gửi phản ánh **không** gắn với điểm thưởng, huy hiệu hay bảng xếp hạng. Huy hiệu chỉ dành cho học tập.
10. Phiếu chưa rõ nhóm vào **hàng chờ phân loại**, không bị loại bỏ.
11. Mọi thao tác phân loại, phân công, phản hồi đều ghi nhật ký.

## 5. Luồng gửi phiếu (chatbot theo kịch bản, không dùng AI)

1. Chọn loại yêu cầu: **phản ánh / đề nghị hỗ trợ / góp ý**
2. Chọn nhóm vấn đề (nút bấm)
3. Hỏi: "Em có đang cần được hỗ trợ ngay không?" → Có: hiện ngay số hỗ trợ và đặt mức **Cần phản hồi ngay**
4. Mô tả, thời gian, địa điểm, đính kèm (giới hạn ở mục 7)
5. Xem bản tóm tắt → chọn **Gửi có danh tính** hoặc **Ẩn danh trên phiếu** → xác nhận
6. Nhận mã phiếu dạng `KN-2026-0001` + xác nhận tự động

Ba trường phân loại độc lập: loại yêu cầu, nhóm vấn đề, mức ưu tiên (2 mức: *Cần phản hồi ngay* / *Phản hồi sau*). Cán bộ được sửa phân loại và nâng ưu tiên. Không chỉ dựa vào từ khóa để quyết định mức ưu tiên.

Trạng thái phiếu: **Đã tiếp nhận → Cần bổ sung → Đang xem xét → Đã có phản hồi**. Không đóng phiếu chỉ vì đã gửi xác nhận. Học sinh và cán bộ trao đổi qua hộp thư gắn mã phiếu, kể cả phiếu ẩn danh.

## 6. Cấu hình định tuyến (dữ liệu giả lập)

**Nhân sự** (email trong tài liệu gốc ghi `abc-trung học.edu.vn`, có dấu cách nên không hợp lệ → dùng `abc-trunghoc.edu.vn` trong code):

| Người | Vai trò | Email |
|---|---|---|
| Nguyễn Minh Anh | Quản trị chính, mở được danh tính | admin@abc-trunghoc.edu.vn |
| Trần Quốc Bảo | Công tác học sinh | baotq@abc-trunghoc.edu.vn |
| Lê Thu Hà | Tư vấn, hỗ trợ học sinh | hatl@abc-trunghoc.edu.vn |
| Phạm Hoàng Nam | Dự phòng | namph@abc-trunghoc.edu.vn |
| Võ Thanh Tùng | Tiếp nhận độc lập, mở được danh tính | tungvt@abc-trunghoc.edu.vn |

**Bảng chuyển phiếu:**

| Nhóm vấn đề | Đầu mối chính | Dự phòng |
|---|---|---|
| Bạo lực học đường, bắt nạt | Trần Quốc Bảo | Phạm Hoàng Nam |
| Thuốc lá điện tử | Trần Quốc Bảo | *(chưa có – xem mục 10)* |
| Vấn đề trên mạng xã hội | Lê Thu Hà | *(chưa có)* |
| Quấy rối, xâm hại | Lê Thu Hà | Võ Thanh Tùng |
| Góp ý hoạt động, môi trường học đường | Trần Quốc Bảo | *(chưa có)* |
| Liên quan trực tiếp người đang xử lý | **Võ Thanh Tùng** (độc lập) | – |
| Khác / chưa rõ | Hàng chờ phân loại | *(chưa có người tiếp nhận chung)* |

Bảng này phải là **cấu hình sửa được trong trang quản trị**, không hard-code trong logic.

## 7. Thời gian, SLA, tệp

- Giờ trực: **08:00–17:00, Thứ Hai–Thứ Sáu**
- Phiếu khẩn: đầu mối xác nhận trong **15 phút**. Quá hạn → email nhắc + chuyển dự phòng.
- Phiếu thường: phản hồi lần đầu trong **2 ngày làm việc**
- Ngoài giờ: vẫn nhận phiếu, hiển thị thời gian dự kiến xử lý + số hỗ trợ **1900 0000** (giả lập). Không để học sinh hiểu là có người trực 24/7.
- Tệp: **PDF, JPG, JPEG, PNG, MP4**; tối đa **10 MB/tệp**, **5 tệp/phiếu**. Kiểm tra ở cả client và server, kiểm tra cả MIME thực, không chỉ đuôi file.
- Nếu dùng WP-Cron cho SLA: cần cron thật trên hosting, vì WP-Cron chỉ chạy khi có lượt truy cập.

## 8. Học tập

Luồng: đăng nhập → chọn chủ đề → học liệu → tình huống (chọn) → giải thích từng lựa chọn → làm lại. Lưu tiến độ theo tài khoản. Học liệu có trạng thái **Nháp / Đã công bố**, học sinh chỉ thấy bản đã công bố.

5 chủ đề chạy thử:

| # | Chủ đề | Tình huống | Câu hỏi kiểm tra |
|---|---|---|---|
| 1 | Bạo lực học đường | Bị trêu chọc, cô lập, xúc phạm trên MXH | Chứng kiến bạn bị bắt nạt, nên làm gì? |
| 2 | Phòng tránh thuốc lá điện tử | Bị bạn rủ dùng trong giờ nghỉ | Xử lý thế nào khi bị rủ rê? |
| 3 | An toàn trên mạng xã hội | Link lạ đòi thông tin cá nhân, tài khoản ngân hàng | Nhận link đáng ngờ, nên làm gì? |
| 4 | Phòng, chống tảo hôn | Bị gia đình ép nghỉ học để cưới | Bản thân / bạn bị ép kết hôn khi chưa đủ tuổi, làm gì? |
| 5 | Phòng, chống quấy rối tình dục | Bị bình phẩm cơ thể, nhắn tin tình dục, đụng chạm | Bị / thấy bạn bị quấy rối, làm gì để an toàn và được hỗ trợ? |

Mỗi chủ đề: tình huống + câu hỏi trắc nghiệm + các phương án + giải thích từng phương án. Chủ đề 4, 5 viết phù hợp lứa tuổi, không mô tả chi tiết hành vi, có hướng dẫn tìm người lớn tin cậy.

## 9. Giao diện

- Mobile-first. Nền **#FFFFFF**, xanh **#1565C0** (điều hướng, nút chính), đỏ **#C62828** (nút "Em cần hỗ trợ", cảnh báo). Chữ tối trên nền trắng.
- Trạng thái luôn có chữ hoặc icon kèm màu (không chỉ dùng màu).
- 3 lối vào chính: **Khám phá tình huống / Em cần hỗ trợ / Gửi góp ý hoặc phản ánh**.
- Header: tên dự án, menu (Trang chủ, Học tương tác, Tài liệu, Gửi phản ánh, Theo dõi phiếu), tìm kiếm học liệu, tài khoản, nút đỏ "Em cần hỗ trợ". **Không có giỏ hàng** → dùng "Tài liệu đã lưu".
- Desktop: nội dung 70–75%, sidebar 25–30%. Mobile: xếp dọc.
- Footer: đơn vị quản lý, liên hệ, chính sách bảo mật, quy tắc sử dụng, giờ tiếp nhận, link giải thích cơ chế ẩn danh.
- Không có quảng cáo.

## 10. Tài khoản thử

| Vai | Đăng nhập | Mật khẩu / ghi chú |
|---|---|---|
| Học sinh | E2400249.test@example.com (tên hiển thị E2400249) | `TestHS2026!`, mã tham gia `HS2026` |
| Quản trị | admin@abc-trunghoc.edu.vn | *chưa có mật khẩu trong tài liệu* |

Mã phiếu mẫu: `KN-2026-0001`.

## 11. Điểm còn mở (hỏi người dùng trước khi tự quyết)

1. **Cách cấp tài khoản**: Brief ghi "nhà trường cấp theo mã học sinh, không tự đăng ký", nhưng dữ liệu thử dùng **email + mã tham gia HS2026** (tự đăng ký). Cần chốt một cách.
2. Thiếu đầu mối cho nhóm **Chất lượng giảng dạy, đào tạo** và **Cơ sở vật chất** (có trong Brief), và thiếu người **tiếp nhận chung** cho phiếu chưa rõ nhóm.
3. Thiếu người dự phòng cho nhóm thuốc lá điện tử, mạng xã hội, góp ý → luật "quá hạn chuyển dự phòng" không chạy được ở các nhóm này.
4. Email trong tài liệu gốc chứa dấu cách (`abc-trung học`), đã tạm sửa thành `abc-trunghoc`.
5. Chưa có mật khẩu cho tài khoản quản trị và tài khoản của 4 cán bộ.
6. Brief nêu 8 chủ đề, bản thử có 5 (thiếu: tin sai sự thật, tiêu cực trong trường, chất lượng đào tạo).

## 12. Checklist kiểm tra dự án (chạy khi được yêu cầu "check dự án")

Kiểm bằng tài khoản thử và tình huống giả lập. Báo cáo theo dạng: ✅ đạt / ❌ lỗi (file:dòng, cách sửa) / ⚠️ chưa làm.

**Tài khoản**
- [ ] Đăng nhập / đăng ký đúng cách đã chốt; sai mã tham gia hoặc sai mật khẩu bị chặn, báo lỗi rõ
- [ ] Đặt lại mật khẩu hoạt động; khóa được tài khoản

**Học tập**
- [ ] Học 1 chủ đề, nhận giải thích theo từng lựa chọn, làm lại được, tiến độ được lưu
- [ ] Giáo viên tải lên, lưu nháp, công bố học liệu; học sinh không thấy bản nháp

**Phiếu**
- [ ] Gửi phiếu có danh tính và phiếu ẩn danh, nhận mã `KN-2026-xxxx`
- [ ] Cán bộ trả lời phiếu ẩn danh mà **không thấy danh tính** ở giao diện, API response, HTML ẩn, email
- [ ] Chỉ Minh Anh / Thanh Tùng mở được danh tính; có bắt nhập lý do và ghi nhật ký
- [ ] Tệp sai định dạng, > 10 MB, > 5 tệp bị chặn ở cả client và server
- [ ] Tệp phía học sinh đã xóa metadata; bản gốc xem được trong quản trị và có nhật ký
- [ ] Phiếu chuyển đúng đầu mối theo bảng mục 6; phiếu liên quan người xử lý → Võ Thanh Tùng
- [ ] Phiếu chưa rõ nhóm vào hàng chờ phân loại
- [ ] Phiếu khẩn: cảnh báo ngay; quá 15 phút chưa xác nhận → nhắc + chuyển dự phòng
- [ ] Ngoài giờ trực: hiển thị giờ tiếp nhận và 1900 0000
- [ ] Học sinh chỉ thấy phiếu của mình; email thông báo không chứa nội dung
- [ ] Bộ lọc quản trị: nhóm, ưu tiên, trạng thái, hạn xử lý, người phụ trách

**Giao diện & bảo mật chung**
- [ ] Hiển thị tốt trên điện thoại (360px); đúng 3 màu; trạng thái có nhãn chữ
- [ ] Không trang công khai nào lộ phiếu hoặc dữ liệu cá nhân
- [ ] Không có secret / mật khẩu thật trong code; input được escape (XSS), truy vấn dùng prepared statement (SQL injection); có CSRF cho form
- [ ] Không có điểm thưởng / huy hiệu gắn với việc gửi phản ánh

**Chỉ số theo dõi khi chạy thử**: số thao tác để gửi 1 phiếu (mục tiêu ≤ 6), % phiếu phải phân loại lại, số phiếu quá hạn, thời gian phản hồi lần đầu. Không dùng số lượng phản ánh làm thành tích thi đua.

## 13. Ngoài phạm vi bản chạy thử

Chatbot AI hiểu câu tự do, AI gợi ý từ tài liệu, SMS/Zalo, diễn đàn mở, sửa kịch bản chatbot trên giao diện, app di động. Không tự thêm các phần này khi chưa được yêu cầu.
