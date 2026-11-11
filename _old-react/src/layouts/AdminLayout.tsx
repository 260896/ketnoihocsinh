import type { ReactNode } from 'react';
import { Link, NavLink } from 'react-router-dom';
import { Icon, type IconName } from '@/components/ui/Icon';
import { site } from '@/config/site';
import { R } from '@/lib/routes';

export type AdminSection = 'Phiếu phản ánh' | 'Học liệu' | 'Cấu hình đầu mối' | 'Tài khoản' | 'Nhật ký';

const items: { label: AdminSection; to: string; icon: IconName }[] = [
  { label: 'Phiếu phản ánh', to: R.qtTickets, icon: 'inbox' },
  { label: 'Học liệu', to: R.qtLearning, icon: 'book' },
  { label: 'Cấu hình đầu mối', to: R.qtConfig, icon: 'settings' },
  { label: 'Tài khoản', to: R.qtAccounts, icon: 'users' },
  { label: 'Nhật ký', to: R.qtAudit, icon: 'history' },
];

export function Sidebar({ active }: { active: AdminSection }) {
  return (
    <nav aria-label="Menu quản trị" className="sidebar">
      <div className="sidebar__brand">
        <span className="brand__logo">
          <Icon name="book" size={18} color="#fff" />
        </span>
        <div className="sidebar__brand-text">
          <p style={{ fontWeight: 600, fontSize: 15 }}>{site.projectName}</p>
          <p style={{ fontSize: 13, opacity: 0.8 }}>Trang quản trị</p>
        </div>
      </div>
      <div className="sidebar__nav">
        {items.map((it) => (
          <NavLink
            key={it.label}
            to={it.to}
            className={`sidebar__link${it.label === active ? ' active' : ''}`}
            aria-current={it.label === active ? 'page' : undefined}
          >
            <Icon name={it.icon} size={20} color="#fff" />
            <span>{it.label}</span>
          </NavLink>
        ))}
      </div>
      <div className="sidebar__user">
        <span className="sidebar__avatar" />
        <div className="sidebar__user-text grow">
          <p style={{ fontSize: 13, fontWeight: 600 }}>[Tên cán bộ]</p>
          <p style={{ fontSize: 13, opacity: 0.8 }}>Cán bộ xử lý</p>
        </div>
        <Link to={R.qtLogin} aria-label="Đăng xuất" className="icon-btn" style={{ minWidth: 36, minHeight: 36 }}>
          <Icon name="logout" size={18} color="#fff" />
        </Link>
      </div>
    </nav>
  );
}

interface AdminLayoutProps {
  section: AdminSection;
  title: string;
  subtitle?: ReactNode;
  actions?: ReactNode;
  children: ReactNode;
}

export function AdminLayout({ section, title, subtitle, actions, children }: AdminLayoutProps) {
  return (
    <div className="a-shell">
      <div className="a-sidebar-col">
        <Sidebar active={section} />
      </div>
      <div className="a-content">
        <header className="a-header">
          <div className="a-header__text">
            <h1 style={{ fontSize: 22, fontWeight: 700 }}>{title}</h1>
            {subtitle && <p className="t-small t-muted">{subtitle}</p>}
          </div>
          {actions}
          <button type="button" aria-label="Thông báo" className="icon-btn icon-btn--bordered">
            <Icon name="bell" size={20} color="var(--c-muted)" />
          </button>
        </header>
        <main className="a-main">{children}</main>
      </div>
    </div>
  );
}
