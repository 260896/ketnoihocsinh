# [TÊN DỰ ÁN] – Học qua tình huống & Gửi phản ánh

Giao diện được dựng từ bản thiết kế canvas (Landing page, 20 màn sinh viên, 9 màn cán bộ quản trị).
Hiện dùng **dữ liệu mẫu trong bộ nhớ**, chưa có backend.

## Chạy

```bash
npm install
npm run dev        # http://localhost:5173
npm run build      # kiểm tra kiểu + build
```

Mở `/man-hinh` để xem mục lục mọi màn hình (mã SVxx / QTxx khớp với bản thiết kế).
Thêm `?ngoaigio=1` vào URL để xem trạng thái ngoài giờ trực.

## Cấu trúc

```
src/
  config/site.ts         Chỗ trống [TÊN DỰ ÁN], [SỐ HỖ TRỢ KHẨN CẤP]…, giờ trực, isOutOfHours()
  styles/                tokens.css (màu, bo góc), global.css (nút, ô nhập, badge…), layouts.css
  components/ui/         Icon, Form (TextField, SelectField, TextAreaField, SwitchRow, Dropzone), Bits (badge, thẻ…)
  layouts/               MobileScreen + TabBar (sinh viên), AdminLayout + Sidebar (cán bộ)
  context/AppStore.tsx   Trạng thái dùng chung: phiếu, bản nháp phiếu, tiến độ học, nhật ký, toast
  data/types.ts          Kiểu dữ liệu (Ticket, Topic, Situation, AuditEntry…)
  data/mock.ts           Dữ liệu mẫu
  lib/routes.ts          Toàn bộ đường dẫn (R.svHome, R.qtTicket(id)…)
  pages/Landing.tsx      Landing page
  pages/student/         Auth (SV01–06), Learn (SV07–10), Account (SV11), Send (SV12–18), Tickets (SV19–20)
  pages/admin/           Login (QT01), Tickets (QT02–04), Learning (QT05–06), Config (QT07), Accounts (QT08), Audit (QT09)
```

## Thêm tính năng / nối backend

- **API**: giữ nguyên chữ ký các hàm trong `AppStore` (`submitDraft`, `addMessage`, `updateTicket`, `log`…)
  và thay phần thân bằng lời gọi API; màn hình không phải sửa. Có thể chuyển sang React Query khi cần cache/loading.
- **Đăng nhập & phân quyền**: hiện nút đăng nhập chỉ điều hướng. Thêm `AuthContext` và route guard cho `/sv/*`, `/qt/*`.
- **Cấu hình** (`site.ts`) nên nạp từ API của màn QT07.
- Mã giả `[MÃ-0012]`, `[Cán bộ A]`… là chỗ trống từ thiết kế, sẽ được thay bằng dữ liệu thật.
