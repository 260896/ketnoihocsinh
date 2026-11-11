import { useNavigate } from 'react-router-dom';
import { Brand, Note } from '@/components/ui/Bits';
import { TextField } from '@/components/ui/Form';
import { site } from '@/config/site';
import { R } from '@/lib/routes';

/* QT01 – Đăng nhập cán bộ */
export default function AdminLogin() {
  const nav = useNavigate();
  return (
    <div
      className="row"
      style={{ minHeight: '100vh', padding: '40px 20px', justifyContent: 'center', background: 'var(--c-bg)' }}
    >
      <form
        className="stack"
        style={{ gap: 18, width: '100%', maxWidth: 460, padding: 40, borderRadius: 20, border: '1px solid var(--c-border)', background: '#fff' }}
        onSubmit={(e) => {
          e.preventDefault();
          nav(R.qtTickets);
        }}
      >
        <Brand />
        <h2 className="t-h1">Đăng nhập trang quản trị</h2>
        <p className="t-small t-muted">Dành cho cán bộ quản trị và cán bộ xử lý phiếu. Tài khoản do quản trị viên cấp.</p>
        <TextField label="Email cán bộ" type="email" placeholder={`canbo@${site.staffEmailDomain}`} autoComplete="username" />
        <TextField label="Mật khẩu" type="password" placeholder="Nhập mật khẩu" autoComplete="current-password" />
        <div className="row gap-12">
          <label className="row gap-8 grow t-small" style={{ cursor: 'pointer' }}>
            <input type="checkbox" className="checkbox" style={{ width: 20, height: 20 }} />
            Ghi nhớ đăng nhập
          </label>
          <a href="#" className="link" onClick={(e) => e.preventDefault()}>
            Quên mật khẩu?
          </a>
        </div>
        <button type="submit" className="btn btn--block">
          Đăng nhập
        </button>
        <div className="panel">
          <Note icon="lock">Mọi thao tác phân loại, phân công, phản hồi và mở danh tính đều được ghi nhật ký.</Note>
        </div>
      </form>
    </div>
  );
}
