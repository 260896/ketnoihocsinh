import { Link } from 'react-router-dom';
import { Icon, type IconName } from '@/components/ui/Icon';
import { MobileScreen } from '@/layouts/MobileScreen';
import { R } from '@/lib/routes';

const items: { icon: IconName; label: string; hint?: string }[] = [
  { icon: 'edit', label: 'Đổi tên hiển thị' },
  { icon: 'lock', label: 'Đổi mật khẩu' },
  { icon: 'mail', label: 'Email thông báo', hint: 'Chỉ báo “phiếu có cập nhật”' },
  { icon: 'file', label: 'Quy tắc sử dụng' },
  { icon: 'lock', label: 'Chính sách bảo mật' },
];

/* SV11 – Tài khoản */
export function StudentAccount() {
  return (
    <MobileScreen title="Tài khoản" tab="Tài khoản">
      <div className="card">
        <div className="row" style={{ gap: 14 }}>
          <span className="icon-circle" style={{ width: 52, height: 52, background: 'var(--c-primary)' }}>
            <Icon name="user" size={26} color="#fff" />
          </span>
          <span>
            <p className="t-h2">[Tên hiển thị]</p>
            <p className="t-small t-muted">ten@email.com</p>
          </span>
        </div>
      </div>
      <div className="card" style={{ padding: '4px 16px', gap: 0 }}>
        {items.map((it, i) => (
          <a
            key={it.label}
            href="#"
            onClick={(e) => e.preventDefault()}
            className="row gap-12"
            style={{
              textDecoration: 'none',
              color: 'var(--c-text)',
              padding: '14px 0',
              borderBottom: i < items.length - 1 ? '1px solid var(--c-border)' : undefined,
            }}
          >
            <Icon name={it.icon} size={20} color="var(--c-primary)" />
            <span className="grow">
              <span className="t-title" style={{ display: 'block' }}>
                {it.label}
              </span>
              {it.hint && <span className="t-small t-muted">{it.hint}</span>}
            </span>
            <Icon name="chevronRight" size={18} color="var(--c-muted)" />
          </a>
        ))}
      </div>
      <Link to={R.svLogin} className="btn btn--danger-outline btn--block">
        Đăng xuất
      </Link>
    </MobileScreen>
  );
}
