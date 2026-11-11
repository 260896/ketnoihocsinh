import type { ReactNode } from 'react';
import { Icon, type IconName } from './Icon';
import { site, telHref } from '@/config/site';
import type { Priority, TicketStatus } from '@/data/types';

export function Brand({ logoColor }: { logoColor?: string }) {
  return (
    <div className="brand">
      <span className="brand__logo" style={logoColor ? { background: logoColor } : undefined}>
        <Icon name="book" size={18} color="#fff" />
      </span>
      <span className="brand__name">{site.projectName}</span>
    </div>
  );
}

/** Thẻ "Cần hỗ trợ ngay?" – gọi số khẩn cấp. */
export function EmergencyCard() {
  return (
    <a href={telHref} className="sos">
      <span className="icon-circle" style={{ width: 40, height: 40, background: 'var(--c-danger)' }}>
        <Icon name="phone" size={20} color="#fff" />
      </span>
      <span className="stack gap-2">
        <span className="t-title t-danger">Cần hỗ trợ ngay?</span>
        <span className="t-small">
          Gọi {site.hotline} · Trực {site.dutyHoursLabel}
        </span>
      </span>
    </a>
  );
}

interface ChoiceCardProps {
  selected: boolean;
  title: ReactNode;
  hint?: ReactNode;
  onSelect: () => void;
}

/** Thẻ lựa chọn dạng radio (dùng cho bước gửi phiếu & tình huống). */
export function ChoiceCard({ selected, title, hint, onSelect }: ChoiceCardProps) {
  return (
    <button
      type="button"
      aria-pressed={selected}
      className={`choice${selected ? ' choice--selected' : ''}`}
      onClick={onSelect}
    >
      <span className="choice__radio" />
      <span className="stack gap-2">
        <span className="t-title">{title}</span>
        {hint && <span className="t-small t-muted">{hint}</span>}
      </span>
    </button>
  );
}

export function Note({ icon, children, color = 'var(--c-primary)' }: { icon: IconName; children: ReactNode; color?: string }) {
  return (
    <div className="note">
      <Icon name={icon} size={18} color={color} />
      <p>{children}</p>
    </div>
  );
}

const statusClass: Record<TicketStatus, string> = {
  'Đã tiếp nhận': 'badge--info',
  'Cần bổ sung': 'badge--danger',
  'Đang xem xét': 'badge--neutral',
  'Đã có phản hồi': 'badge--solid',
};

export function StatusBadge({ status }: { status: TicketStatus }) {
  return <span className={`badge badge--lg ${statusClass[status]}`}>{status}</span>;
}

const priorityClass: Record<Priority, string> = {
  Khẩn: 'badge--danger-solid',
  Cao: 'badge--danger',
  Thường: 'badge--muted',
};

export function PriorityBadge({ priority }: { priority: Priority }) {
  return <span className={`badge badge--square ${priorityClass[priority]}`}>{priority}</span>;
}

export function PublishBadge({ status }: { status: 'Đã công bố' | 'Nháp' }) {
  return <span className={`badge ${status === 'Đã công bố' ? 'badge--solid' : 'badge--neutral'}`}>{status}</span>;
}
