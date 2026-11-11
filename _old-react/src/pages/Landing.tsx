import { useState } from 'react';
import { Link } from 'react-router-dom';
import { Icon, type IconName } from '@/components/ui/Icon';
import { isOutOfHours, site, telHref } from '@/config/site';
import { topics } from '@/data/mock';
import { R } from '@/lib/routes';
import './landing.css';

const demoOptions = [
  'A. Thả cảm xúc cho vui, ai cũng làm vậy',
  'B. Không chia sẻ tiếp, hỏi thăm bạn và báo người phụ trách',
  'C. Im lặng, coi như không thấy',
];

const sendSteps = [
  { t: 'Chọn loại yêu cầu', d: 'Phản ánh, đề nghị hỗ trợ hoặc góp ý.' },
  { t: 'Chọn nhóm vấn đề', d: 'Chưa chắc nhóm nào? Phiếu vẫn được cán bộ phân loại.' },
  { t: 'Cần hỗ trợ ngay không?', d: 'Chọn “Có”, số hỗ trợ khẩn cấp hiện ra ngay.', danger: true },
  { t: 'Mô tả sự việc', d: `Thời gian, địa điểm, đính kèm tệp (${site.fileTypesLabel}, tối đa ${site.maxFileSizeLabel}).` },
  { t: 'Xem lại và gửi', d: 'Chọn gửi có danh tính hoặc ẩn danh, rồi xác nhận.' },
];

const privacy: { icon: IconName; t: string; d: string }[] = [
  { icon: 'eyeOff', t: 'Cán bộ xử lý', d: 'Đọc nội dung phiếu và trả lời bạn, không thấy danh tính với phiếu ẩn danh.' },
  { icon: 'lock', t: 'Người được chỉ định', d: 'Chỉ họ mở được danh tính. Mỗi lần mở đều được ghi nhật ký.' },
  { icon: 'file', t: 'Tệp đính kèm', d: 'Bản tải về đã xóa GPS, thiết bị, thời điểm chụp. Bản gốc chỉ cán bộ có thẩm quyền xem để xác minh, có ghi nhật ký.' },
  { icon: 'user', t: 'Đăng ký tối giản', d: 'Chỉ cần email và tên hiển thị, có thể là biệt danh. Không hỏi họ tên thật, mã sinh viên hay lớp.' },
];

const startSteps = [
  { t: 'Nhập mã tham gia', d: 'Mã do ban tổ chức phát tại buổi ngoại khóa.' },
  { t: 'Email và tên hiển thị', d: 'Tên hiển thị có thể là biệt danh.' },
  { t: 'Xác thực email', d: 'Bấm liên kết gửi về hộp thư để kích hoạt tài khoản.' },
];

export default function Landing() {
  const [demoPick, setDemoPick] = useState(1);
  const outOfHours = isOutOfHours();

  return (
    <div className="lp">
      {outOfHours && (
        <div className="lp-banner">
          <span style={{ fontWeight: 700 }}>Ngoài giờ trực</span>
          <span>
            Phiếu gửi bây giờ sẽ được tiếp nhận từ {site.dutyStartLabel}. Cần hỗ trợ ngay, gọi{' '}
            <a href={telHref}>{site.hotline}</a>
          </span>
        </div>
      )}

      <header className="lp-header">
        <div className="lp-container lp-header__inner">
          <a href="#top" className="lp-logo">
            <span className="brand__logo">
              <Icon name="graduation" size={20} color="#fff" />
            </span>
            <span className="brand__name">{site.projectName}</span>
          </a>
          <nav aria-label="Điều hướng chính" className="lp-nav">
            <Link to={R.svHome}>Học tình huống</Link>
            <Link to={R.svSend}>Gửi phản ánh</Link>
            <a href="#an-danh">Quyền riêng tư</a>
            <Link to={R.svTickets}>Theo dõi phiếu</Link>
            <Link to={R.svLogin} className="lp-nav__login">
              Đăng nhập
            </Link>
          </nav>
        </div>
      </header>

      {/* Hero */}
      <section id="top" className="lp-hero">
        <div className="lp-container lp-hero__inner">
          <div className="lp-hero__text">
            <span className="lp-pill">Dành cho sinh viên tham gia {site.activityName}</span>
            <h1>
              Học cách ứng xử qua tình huống thật. <span>Lên tiếng an toàn</span> khi cần.
            </h1>
            <p className="lp-lead">
              Làm các tình huống trắc nghiệm về bạo lực học đường, thuốc lá điện tử và ứng xử trên mạng xã hội. Gửi phản
              ánh, đề nghị hỗ trợ hoặc góp ý, có thể ẩn danh, và nhận phản hồi từ cán bộ phụ trách.
            </p>
            <div className="row wrap gap-12">
              <Link to={R.svHome} className="lp-cta">
                Bắt đầu học
              </Link>
              <Link to={R.svSend} className="lp-cta lp-cta--danger-outline">
                Gửi phản ánh
              </Link>
            </div>
            <p style={{ fontSize: 14, color: 'var(--c-muted)' }}>
              Cần <strong>mã tham gia</strong> do ban tổ chức phát trong buổi ngoại khóa để đăng ký.
            </p>
          </div>

          <div className="lp-demo-col">
            <div className="lp-demo">
              <div className="row justify-between gap-8" style={{ fontSize: 13, color: 'var(--c-muted)' }}>
                <span style={{ fontWeight: 600, color: 'var(--c-primary)' }}>Ứng xử trên mạng xã hội</span>
                <span>Tình huống 2/5</span>
              </div>
              <div style={{ height: 6, background: 'var(--c-primary-100)', borderRadius: 999 }}>
                <div style={{ width: '40%', height: 6, background: 'var(--c-primary)', borderRadius: 999 }} />
              </div>
              <p style={{ fontWeight: 700, fontSize: 17, lineHeight: 1.45 }}>
                Một bài đăng chế giễu bạn cùng lớp đang được chia sẻ trong nhóm chat. Bạn sẽ làm gì?
              </p>
              <div className="stack gap-10">
                {demoOptions.map((o, i) => (
                  <button
                    key={o}
                    type="button"
                    aria-pressed={demoPick === i}
                    className={`lp-demo__opt${demoPick === i ? ' on' : ''}`}
                    onClick={() => setDemoPick(i)}
                  >
                    {o}
                  </button>
                ))}
              </div>
              <div className="lp-demo__exp">
                <strong>Giải thích:</strong>{' '}
                {demoPick === 1
                  ? 'Dừng lan truyền và hỗ trợ người bị chế giễu giúp hạn chế tổn thương.'
                  : 'Lựa chọn này có thể khiến người bị chế giễu tổn thương thêm.'}{' '}
                [Phần giải thích do cán bộ nhập]
              </div>
              <span style={{ fontSize: 12, color: 'var(--c-muted)', textAlign: 'center' }}>Ví dụ minh họa giao diện</span>
            </div>
          </div>
        </div>
      </section>

      {/* Hỗ trợ khẩn cấp */}
      <section aria-label="Hỗ trợ khẩn cấp">
        <div className="lp-container lp-sos-wrap">
          <div className="lp-sos">
            <span className="lp-sos__icon">
              <Icon name="phone" size={22} color="var(--c-danger)" />
            </span>
            <div style={{ flex: '1 1 320px', minWidth: 0 }}>
              <p style={{ fontWeight: 700, color: 'var(--c-danger)' }}>Bạn đang gặp nguy hiểm hoặc cần hỗ trợ ngay?</p>
              <p style={{ fontSize: 14, color: 'var(--c-text-2)' }}>
                Gọi ngay số hỗ trợ khẩn cấp, không cần chờ gửi phiếu. Giờ trực: {site.dutyHoursLabel}.
              </p>
            </div>
            <a href={telHref} className="lp-cta lp-cta--danger">
              Gọi {site.hotline}
            </a>
          </div>
        </div>
      </section>

      {/* Học qua tình huống */}
      <section id="hoc">
        <div className="lp-container lp-section" style={{ paddingTop: 80, paddingBottom: 40 }}>
          <div className="lp-head">
            <span className="lp-eyebrow">Học qua tình huống</span>
            <h2 className="lp-h2">Chọn một chủ đề, trả lời và xem giải thích cho từng lựa chọn</h2>
            <p className="lp-sub">Tiến độ được lưu theo tài khoản. Bạn có thể làm lại bất cứ lúc nào.</p>
          </div>
          <div className="lp-topics">
            {topics.map((t) => (
              <Link key={t.id} to={R.svTopic(t.id)} className="lp-topic">
                <span className="lp-topic__icon">
                  <Icon name={t.icon} size={24} color="var(--c-primary)" />
                </span>
                <span className="lp-topic__name">{t.name}</span>
                <span className="lp-topic__desc">[Mô tả ngắn chủ đề]</span>
                <span className="lp-topic__foot">
                  <span>{t.situations.length} tình huống</span>
                  <span>Bắt đầu →</span>
                </span>
              </Link>
            ))}
          </div>
        </div>
      </section>

      {/* Gửi phiếu */}
      <section id="phieu">
        <div className="lp-container lp-section" style={{ paddingTop: 56, paddingBottom: 56 }}>
          <div className="lp-head">
            <span className="lp-eyebrow" style={{ color: 'var(--c-danger)' }}>
              Gửi phiếu
            </span>
            <h2 className="lp-h2">Phản ánh, đề nghị hỗ trợ hoặc góp ý trong 5 bước</h2>
            <p className="lp-sub">Biểu mẫu hỏi từng bước. Bạn xem lại toàn bộ trước khi gửi.</p>
          </div>
          <ol className="lp-steps">
            {sendSteps.map((s, i) => (
              <li key={s.t} className={`lp-step${s.danger ? ' lp-step--danger' : ''}`}>
                <span className="lp-step__num">{i + 1}</span>
                <span style={{ fontWeight: 700 }}>{s.t}</span>
                <span className="lp-step__desc">{s.d}</span>
              </li>
            ))}
          </ol>
          <div>
            <Link to={R.svSend} className="lp-cta lp-cta--danger">
              Gửi phiếu mới
            </Link>
          </div>
        </div>
      </section>

      {/* Ẩn danh */}
      <section id="an-danh" className="lp-dark">
        <div className="lp-container lp-dark__inner">
          <div className="stack gap-14" style={{ flex: '1 1 340px', minWidth: 0 }}>
            <span className="lp-eyebrow">Ẩn danh và quyền riêng tư</span>
            <h2 className="lp-h2">Nói rõ ai thấy gì, trước khi bạn gửi</h2>
            <p style={{ fontSize: 16, color: 'var(--c-on-dark-muted)' }}>
              Ẩn danh nghĩa là cán bộ xử lý không thấy tên và email của bạn. Hệ thống vẫn lưu liên kết với tài khoản ở nơi
              riêng, để người có thẩm quyền mở khi thật sự cần theo quy tắc {site.unit} ban hành.
            </p>
          </div>
          <div className="lp-dark__grid">
            {privacy.map((p) => (
              <div key={p.t} className="lp-dark__card">
                <Icon name={p.icon} size={24} color="var(--c-on-dark-accent)" />
                <span style={{ fontWeight: 700 }}>{p.t}</span>
                <span>{p.d}</span>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Theo dõi phiếu */}
      <section id="theo-doi">
        <div className="lp-container lp-track">
          <div className="stack gap-14" style={{ flex: '1 1 400px', minWidth: 0 }}>
            <span className="lp-eyebrow">Theo dõi phiếu</span>
            <h2 className="lp-h2">Mỗi phiếu có mã riêng và hộp thư trao đổi</h2>
            <p className="lp-sub">
              Sau khi gửi, bạn nhận mã phiếu và một xác nhận tự động. Cán bộ trao đổi với bạn qua hộp thư gắn với mã phiếu,
              kể cả phiếu ẩn danh.
            </p>
            <p style={{ color: 'var(--c-text-2)', fontSize: 15 }}>
              Email chỉ báo “phiếu có cập nhật”, không kèm nội dung. Đăng nhập để đọc chi tiết.
            </p>
          </div>
          <div className="lp-track__demo">
            <div className="row wrap justify-between gap-8">
              <span style={{ fontWeight: 700, fontSize: 17 }}>Phiếu #[MÃ PHIẾU]</span>
              <span style={{ fontSize: 13, color: 'var(--c-muted)' }}>Đề nghị hỗ trợ · Ẩn danh</span>
            </div>
            <ol aria-label="Trạng thái phiếu" className="lp-status">
              <li style={{ background: 'var(--c-primary)', color: '#fff' }}>Đã tiếp nhận</li>
              <li style={{ background: 'var(--c-danger-50)', color: 'var(--c-danger)' }}>Cần bổ sung</li>
              <li>Đang xem xét</li>
              <li>Đã có phản hồi</li>
            </ol>
            <div className="lp-chat">
              <div className="lp-msg">
                <b style={{ color: 'var(--c-muted)' }}>Xác nhận tự động</b>
                Hệ thống đã nhận phiếu của bạn. Đây chưa phải phản hồi của cán bộ.
              </div>
              <div className="lp-msg">
                <b style={{ color: 'var(--c-primary)' }}>Cán bộ phụ trách</b>
                [Nội dung trao đổi của cán bộ]
              </div>
              <div className="lp-msg lp-msg--me">[Tin nhắn của sinh viên]</div>
            </div>
            <div className="row gap-8">
              <label htmlFor="lp-reply" className="sr-only">
                Nhập tin nhắn
              </label>
              <input id="lp-reply" type="text" placeholder="Nhập tin nhắn…" className="input grow" style={{ fontSize: 14, borderWidth: 1 }} />
              <Link to={R.svLogin} className="btn" style={{ minHeight: 44, padding: '0 18px', fontSize: 14 }}>
                Gửi
              </Link>
            </div>
          </div>
        </div>
      </section>

      {/* Bắt đầu */}
      <section id="dang-nhap" className="lp-start">
        <div className="lp-container lp-start__inner">
          <h2 className="lp-h2" style={{ maxWidth: 640 }}>
            Bắt đầu trong 3 bước
          </h2>
          <ol className="lp-start__steps">
            {startSteps.map((s, i) => (
              <li key={s.t} className="lp-start__step">
                <span className="lp-start__num">{i + 1}</span>
                <span>
                  <strong style={{ display: 'block' }}>{s.t}</strong>
                  <span style={{ fontSize: 14, color: 'var(--c-text-2)' }}>{s.d}</span>
                </span>
              </li>
            ))}
          </ol>
          <div className="row wrap gap-12" style={{ justifyContent: 'center' }}>
            <Link to={R.svRegister} className="lp-cta">
              Đăng ký tài khoản
            </Link>
            <Link to={R.svLogin} className="lp-cta lp-cta--outline">
              Tôi đã có tài khoản
            </Link>
          </div>
        </div>
      </section>

      <footer className="lp-footer">
        <div className="lp-container lp-footer__inner">
          <div className="lp-footer__col" style={{ flex: '1 1 280px' }}>
            <span style={{ fontWeight: 800, color: 'var(--c-text)', fontSize: 16 }}>{site.projectName}</span>
            <span>Đơn vị quản lý: {site.websiteOrg}</span>
            <span>Thời gian tiếp nhận: {site.dutyHoursLabel}</span>
          </div>
          <div className="lp-footer__col" style={{ flex: '1 1 220px' }}>
            <span style={{ fontWeight: 700, color: 'var(--c-text)' }}>Hỗ trợ khẩn cấp</span>
            <a href={telHref} style={{ color: 'var(--c-danger)', fontWeight: 700, textDecoration: 'none', fontSize: 18 }}>
              {site.hotline}
            </a>
          </div>
          <nav aria-label="Liên kết chân trang" className="lp-footer__col" style={{ flex: '1 1 200px' }}>
            <a href="#top">Chính sách bảo mật</a>
            <a href="#top">Quy tắc sử dụng</a>
            <a href="#an-danh">Ai xem được phiếu của tôi?</a>
          </nav>
          <div className="lp-footer__col" style={{ flex: '1 1 160px' }}>
            <span style={{ fontWeight: 700, color: 'var(--c-text)' }}>Cán bộ</span>
            <Link to={R.qtLogin}>Đăng nhập trang quản trị</Link>
          </div>
        </div>
      </footer>
    </div>
  );
}
