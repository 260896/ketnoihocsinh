import { useEffect, useState, type FormEvent } from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import { Brand, EmergencyCard, Note } from '@/components/ui/Bits';
import { Icon } from '@/components/ui/Icon';
import { TextField } from '@/components/ui/Form';
import { site } from '@/config/site';
import { VALID_JOIN_CODES } from '@/data/mock';
import { MobileScreen } from '@/layouts/MobileScreen';
import { R } from '@/lib/routes';

/* SV01 + SV02 – Đăng ký (kèm trạng thái sai mã tham gia) */
export function Register() {
  const nav = useNavigate();
  const [form, setForm] = useState({ code: '', email: '', name: '', pw: '', pw2: '' });
  const [agree, setAgree] = useState(true);
  const [errors, setErrors] = useState<Partial<Record<keyof typeof form, string>>>({});
  const set = (k: keyof typeof form) => (e: React.ChangeEvent<HTMLInputElement>) =>
    setForm((f) => ({ ...f, [k]: e.target.value }));

  const submit = (e: FormEvent) => {
    e.preventDefault();
    const err: typeof errors = {};
    if (!VALID_JOIN_CODES.includes(form.code.trim().toUpperCase()))
      err.code = 'Mã tham gia không đúng hoặc đã hết hạn. Liên hệ ban tổ chức để lấy mã mới.';
    if (!/^\S+@\S+\.\S+$/.test(form.email)) err.email = 'Email chưa đúng định dạng.';
    if (!form.name.trim()) err.name = 'Nhập tên hiển thị (có thể là biệt danh).';
    if (form.pw.length < 8) err.pw = 'Mật khẩu tối thiểu 8 ký tự.';
    if (form.pw2 !== form.pw) err.pw2 = 'Mật khẩu nhập lại chưa khớp.';
    setErrors(err);
    if (Object.keys(err).length === 0 && agree) nav(R.svVerify, { state: { email: form.email } });
  };

  return (
    <MobileScreen title="Đăng ký" back={R.svLogin}>
      <form className="stack gap-16" onSubmit={submit} noValidate>
        <Brand />
        <h2 className="t-h1">Tạo tài khoản</h2>
        <p className="t-muted">Dùng mã tham gia do ban tổ chức phát trong buổi ngoại khóa.</p>
        <TextField label="Mã tham gia *" placeholder="Ví dụ: NK2026-XXXX" value={form.code} onChange={set('code')} error={errors.code} />
        <TextField label="Email *" type="email" placeholder="ten@email.com" value={form.email} onChange={set('email')} error={errors.email} autoComplete="email" />
        <TextField label="Tên hiển thị *" placeholder="Có thể là biệt danh" value={form.name} onChange={set('name')} error={errors.name} />
        <TextField label="Mật khẩu *" type="password" placeholder="Tối thiểu 8 ký tự" value={form.pw} onChange={set('pw')} error={errors.pw} autoComplete="new-password" />
        <TextField label="Nhập lại mật khẩu *" type="password" placeholder="Nhập lại mật khẩu" value={form.pw2} onChange={set('pw2')} error={errors.pw2} autoComplete="new-password" />
        <div className="panel">
          <Note icon="eyeOff">Không cần họ tên thật, mã sinh viên hay lớp.</Note>
        </div>
        <label className="note" style={{ cursor: 'pointer' }}>
          <input type="checkbox" className="checkbox" checked={agree} onChange={(e) => setAgree(e.target.checked)} />
          <span>Tôi đồng ý với Quy tắc sử dụng và Chính sách bảo mật của {site.orgName}.</span>
        </label>
        <button type="submit" className="btn btn--block" disabled={!agree}>
          Đăng ký
        </button>
        <Link to={R.svLogin} className="link link--lg t-center" style={{ display: 'block' }}>
          Đã có tài khoản? Đăng nhập
        </Link>
      </form>
    </MobileScreen>
  );
}

/* SV03 – Xác thực email */
export function VerifyEmail() {
  const [wait, setWait] = useState(60);
  useEffect(() => {
    if (wait <= 0) return;
    const t = window.setTimeout(() => setWait((w) => w - 1), 1000);
    return () => window.clearTimeout(t);
  }, [wait]);
  const email = (useLocation().state as { email?: string } | null)?.email || 'ten@email.com';

  return (
    <MobileScreen title="Xác thực email">
      <div className="stack gap-14 items-center" style={{ paddingTop: 36 }}>
        <span className="icon-circle" style={{ width: 72, height: 72, background: 'var(--c-primary-50)' }}>
          <Icon name="mail" size={36} color="var(--c-primary)" />
        </span>
        <h2 className="t-h1 t-center">Kiểm tra hộp thư của bạn</h2>
        <p className="t-muted t-center">
          Liên kết xác thực đã gửi tới {email}. Tài khoản chỉ dùng được sau khi xác thực.
        </p>
        <div className="panel self-stretch">
          <Note icon="info">
            Không thấy email? Kiểm tra mục Thư rác/Spam. Liên kết có hiệu lực trong {site.verifyLinkTtl}.
          </Note>
        </div>
        <button type="button" className="btn btn--outline btn--block" disabled={wait > 0} onClick={() => setWait(60)}>
          {wait > 0 ? `Gửi lại liên kết (sau ${wait} giây)` : 'Gửi lại liên kết'}
        </button>
        <Link to={R.svLogin} className="btn btn--ghost btn--block">
          Đã xác thực – Đăng nhập
        </Link>
      </div>
    </MobileScreen>
  );
}

/* SV04 – Đăng nhập */
export function Login() {
  const nav = useNavigate();
  return (
    <MobileScreen noHeader>
      <form
        className="stack"
        style={{ gap: 18, paddingTop: 28 }}
        onSubmit={(e) => {
          e.preventDefault();
          nav(R.svHome);
        }}
      >
        <Brand />
        <h2 style={{ fontSize: 28, fontWeight: 700, lineHeight: 1.3 }}>Đăng nhập</h2>
        <p className="t-muted">Học qua tình huống và gửi phản ánh an toàn.</p>
        <TextField label="Email" type="email" placeholder="ten@email.com" autoComplete="email" />
        <TextField label="Mật khẩu" type="password" placeholder="Nhập mật khẩu" autoComplete="current-password" />
        <div style={{ textAlign: 'right' }}>
          <Link to={R.svForgot} className="link link--lg">
            Quên mật khẩu?
          </Link>
        </div>
        <button type="submit" className="btn btn--block">
          Đăng nhập
        </button>
        <Link to={R.svRegister} className="link link--lg t-center" style={{ display: 'block' }}>
          Chưa có tài khoản? Đăng ký bằng mã tham gia
        </Link>
        <div style={{ height: 12 }} />
        <EmergencyCard />
      </form>
    </MobileScreen>
  );
}

/* SV05 – Quên mật khẩu */
export function ForgotPassword() {
  const [sent, setSent] = useState(false);
  return (
    <MobileScreen title="Quên mật khẩu" back={R.svLogin}>
      <form
        className="stack gap-16"
        onSubmit={(e) => {
          e.preventDefault();
          setSent(true);
        }}
      >
        <span className="icon-circle" style={{ width: 56, height: 56, background: 'var(--c-primary-50)' }}>
          <Icon name="key" size={28} color="var(--c-primary)" />
        </span>
        <h2 className="t-h1">Đặt lại mật khẩu</h2>
        <p className="t-muted">Nhập email đã đăng ký. Chúng tôi sẽ gửi liên kết đặt lại mật khẩu.</p>
        <TextField label="Email" type="email" placeholder="ten@email.com" autoComplete="email" />
        <button type="submit" className="btn btn--block">
          Gửi liên kết đặt lại
        </button>
        {sent && (
          <div className="panel panel--info" role="status">
            <Note icon="check">Nếu email có trong hệ thống, liên kết đặt lại đã được gửi. Kiểm tra cả thư rác.</Note>
          </div>
        )}
        {sent && (
          <Link to={R.svNewPassword} className="link link--lg t-center" style={{ display: 'block' }}>
            (Xem trước) Mở liên kết đặt lại
          </Link>
        )}
      </form>
    </MobileScreen>
  );
}

/* SV06 – Mật khẩu mới */
export function NewPassword() {
  const nav = useNavigate();
  const [pw, setPw] = useState('');
  const [pw2, setPw2] = useState('');
  const [err, setErr] = useState<string | null>(null);
  return (
    <MobileScreen title="Mật khẩu mới">
      <form
        className="stack gap-16"
        onSubmit={(e) => {
          e.preventDefault();
          if (pw.length < 8 || !/[a-zA-Z]/.test(pw) || !/\d/.test(pw)) return setErr('Mật khẩu chưa đủ yêu cầu.');
          if (pw !== pw2) return setErr('Mật khẩu nhập lại chưa khớp.');
          nav(R.svLogin);
        }}
      >
        <span className="icon-circle" style={{ width: 56, height: 56, background: 'var(--c-primary-50)' }}>
          <Icon name="lock" size={28} color="var(--c-primary)" />
        </span>
        <h2 className="t-h1">Tạo mật khẩu mới</h2>
        <TextField label="Mật khẩu mới" type="password" placeholder="Tối thiểu 8 ký tự" value={pw} onChange={(e) => setPw(e.target.value)} autoComplete="new-password" />
        <TextField label="Nhập lại mật khẩu mới" type="password" placeholder="Nhập lại mật khẩu" value={pw2} onChange={(e) => setPw2(e.target.value)} error={err} autoComplete="new-password" />
        <p className="t-small t-muted">Gồm ít nhất 8 ký tự, có chữ và số.</p>
        <button type="submit" className="btn btn--block">
          Lưu mật khẩu
        </button>
      </form>
    </MobileScreen>
  );
}
