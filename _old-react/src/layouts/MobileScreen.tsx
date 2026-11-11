import type { ReactNode } from 'react';
import { Link, NavLink } from 'react-router-dom';
import { Icon, type IconName } from '@/components/ui/Icon';
import { R } from '@/lib/routes';

export type StudentTab = 'Học' | 'Gửi phiếu' | 'Phiếu của tôi' | 'Tài khoản';

interface MobileScreenProps {
  title?: string;
  /** Đường dẫn nút quay lại. Bỏ trống = không có nút. */
  back?: string;
  /** Ẩn thanh tiêu đề (vd. màn đăng nhập). */
  noHeader?: boolean;
  footer?: ReactNode;
  tab?: StudentTab;
  mainClassName?: string;
  children: ReactNode;
}

/** Khung màn hình điện thoại cho sinh viên: header – nội dung cuộn – footer – tab bar. */
export function MobileScreen({ title, back, noHeader, footer, tab, children }: MobileScreenProps) {
  return (
    <div className="m-shell">
      <div className="m-screen">
        {!noHeader && (
          <header className="m-header">
            {back ? (
              <Link to={back} aria-label="Quay lại" className="icon-btn">
                <Icon name="chevronLeft" size={22} />
              </Link>
            ) : (
              <span className="m-header__spacer" />
            )}
            <h1 className="m-header__title">{title}</h1>
          </header>
        )}
        <main className="m-main">{children}</main>
        {footer && <div className="m-footer">{footer}</div>}
        {tab && <TabBar active={tab} />}
      </div>
    </div>
  );
}

const tabs: { label: StudentTab; to: string; icon: IconName }[] = [
  { label: 'Học', to: R.svHome, icon: 'book' },
  { label: 'Gửi phiếu', to: R.svSend, icon: 'send' },
  { label: 'Phiếu của tôi', to: R.svTickets, icon: 'inbox' },
  { label: 'Tài khoản', to: R.svAccount, icon: 'user' },
];

export function TabBar({ active }: { active: StudentTab }) {
  return (
    <nav aria-label="Điều hướng chính" className="tabbar">
      {tabs.map((t) => (
        <NavLink
          key={t.label}
          to={t.to}
          className={`tabbar__item${t.label === active ? ' active' : ''}`}
          aria-current={t.label === active ? 'page' : undefined}
        >
          <Icon name={t.icon} size={22} />
          <span>{t.label}</span>
        </NavLink>
      ))}
    </nav>
  );
}

/** Thanh tiến trình 5 bước của luồng gửi phiếu. */
export function StepProgress({ step, total = 5 }: { step: number; total?: number }) {
  return (
    <div className="stack gap-8">
      <div className="steps" aria-hidden="true">
        {Array.from({ length: total }, (_, i) => (
          <span key={i} className={i < step ? 'on' : ''} />
        ))}
      </div>
      <p className="t-small t-strong t-primary">
        Bước {step}/{total}
      </p>
    </div>
  );
}
