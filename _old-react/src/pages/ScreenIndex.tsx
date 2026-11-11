import { Link } from 'react-router-dom';
import { R } from '@/lib/routes';

/** Trang mục lục màn hình (dùng khi phát triển/duyệt giao diện) – /man-hinh */
const groups: { title: string; items: [string, string, string][] }[] = [
  {
    title: 'Landing page',
    items: [['—', 'Landing page', R.landing], ['—', 'Landing – ngoài giờ trực', '/?ngoaigio=1']],
  },
  {
    title: 'Sinh viên – Tài khoản',
    items: [
      ['SV01–02', 'Đăng ký (nhập sai mã để xem lỗi)', R.svRegister],
      ['SV03', 'Xác thực email', R.svVerify],
      ['SV04', 'Đăng nhập', R.svLogin],
      ['SV05', 'Quên mật khẩu', R.svForgot],
      ['SV06', 'Mật khẩu mới', R.svNewPassword],
    ],
  },
  {
    title: 'Sinh viên – Học',
    items: [
      ['SV07', 'Trang chủ (Học)', R.svHome],
      ['SV08', 'Chủ đề', R.svTopic('bao-luc-hoc-duong')],
      ['SV09', 'Tình huống & giải thích', R.svSituation('bao-luc-hoc-duong', 3)],
      ['SV10', 'Hoàn thành chủ đề', R.svTopicDone('ung-xu-mang-xa-hoi')],
      ['SV11', 'Tài khoản', R.svAccount],
    ],
  },
  {
    title: 'Sinh viên – Gửi phiếu',
    items: [
      ['SV12–16', 'Gửi phiếu 5 bước', R.svSendStep(1)],
      ['SV18', 'Ngoài giờ trực', R.svOutOfHours],
      ['SV19', 'Phiếu của tôi', R.svTickets],
      ['SV20', 'Chi tiết phiếu & hộp thư', R.svTicket('[MÃ-0012]')],
    ],
  },
  {
    title: 'Cán bộ quản trị',
    items: [
      ['QT01', 'Đăng nhập cán bộ', R.qtLogin],
      ['QT02', 'Danh sách phiếu', R.qtTickets],
      ['QT03', 'Xử lý phiếu', R.qtTicket('[MÃ-0012]')],
      ['QT04', 'Form mở danh tính', R.qtReveal('[MÃ-0012]')],
      ['QT05', 'Học liệu', R.qtLearning],
      ['QT06', 'Soạn tình huống', R.qtNewSituation],
      ['QT07', 'Cấu hình đầu mối', R.qtConfig],
      ['QT08', 'Tài khoản', R.qtAccounts],
      ['QT09', 'Nhật ký thao tác', R.qtAudit],
    ],
  },
];

export default function ScreenIndex() {
  return (
    <div style={{ maxWidth: 880, margin: '0 auto', padding: '40px 20px' }} className="stack gap-20">
      <h1 className="t-h1">Mục lục màn hình</h1>
      {groups.map((g) => (
        <section key={g.title} className="card card--admin">
          <h2 className="t-h2">{g.title}</h2>
          <ul style={{ margin: 0, paddingLeft: 0, listStyle: 'none' }} className="stack gap-8">
            {g.items.map(([code, name, to]) => (
              <li key={to} className="row gap-12">
                <span className="badge badge--info badge--square" style={{ minWidth: 72, textAlign: 'center' }}>
                  {code}
                </span>
                <Link to={to}>{name}</Link>
              </li>
            ))}
          </ul>
        </section>
      ))}
    </div>
  );
}
