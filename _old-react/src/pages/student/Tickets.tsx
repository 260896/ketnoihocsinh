import { useEffect, useRef, useState } from 'react';
import { Link, Navigate, useParams } from 'react-router-dom';
import { StatusBadge } from '@/components/ui/Bits';
import { Icon } from '@/components/ui/Icon';
import { useStore } from '@/context/AppStore';
import type { TicketStatus } from '@/data/types';
import { MobileScreen } from '@/layouts/MobileScreen';
import { R } from '@/lib/routes';

/* SV19 – Phiếu của tôi */
export function MyTickets() {
  const { tickets, myTicketIds } = useStore();
  const [q, setQ] = useState('');
  const mine = myTicketIds
    .map((id) => tickets.find((t) => t.id === id))
    .filter((t) => t && t.id.toLowerCase().includes(q.trim().toLowerCase().replace(/^#/, '')));

  return (
    <MobileScreen title="Phiếu của tôi" tab="Phiếu của tôi">
      <div className="row gap-10" style={{ padding: '12px 14px', borderRadius: 12, background: 'var(--c-bg)' }}>
        <Icon name="search" size={18} color="var(--c-muted)" />
        <label htmlFor="q" className="sr-only">
          Tìm theo mã phiếu
        </label>
        <input
          id="q"
          placeholder="Tìm theo mã phiếu"
          value={q}
          onChange={(e) => setQ(e.target.value)}
          style={{ all: 'unset', flex: '1 1 auto', fontSize: 15 }}
        />
      </div>
      {mine.length === 0 && <p className="t-muted t-center">Không tìm thấy phiếu.</p>}
      {mine.map(
        (t) =>
          t && (
            <Link key={t.id} to={R.svTicket(t.id)} className="list-card">
              <div className="row gap-8">
                <p className="t-title grow">#{t.id}</p>
                {t.hasUpdate && <span className="dot" aria-label="Có cập nhật mới" role="img" />}
                <Icon name="chevronRight" size={18} color="var(--c-muted)" />
              </div>
              <p className="t-small t-muted">
                {t.type} · {t.anonymous ? 'Ẩn danh' : 'Có danh tính'}
              </p>
              <div className="row gap-8 wrap">
                <StatusBadge status={t.status} />
                {t.studentNote && <p className={`t-small ${t.status === 'Cần bổ sung' ? 't-danger' : 't-muted'}`}>{t.studentNote}</p>}
              </div>
            </Link>
          ),
      )}
    </MobileScreen>
  );
}

const steps: { s: TicketStatus; label: string }[] = [
  { s: 'Đã tiếp nhận', label: 'Đã tiếp nhận' },
  { s: 'Cần bổ sung', label: 'Cần bổ sung' },
  { s: 'Đang xem xét', label: 'Đang xem xét' },
  { s: 'Đã có phản hồi', label: 'Đã phản hồi' },
];

/* SV20 – Chi tiết phiếu & hộp thư */
export function TicketDetail() {
  const { id = '' } = useParams();
  const { getTicket, addMessage, updateTicket } = useStore();
  const t = getTicket(id);
  const [msg, setMsg] = useState('');
  const endRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (t?.hasUpdate) updateTicket(t.id, { hasUpdate: false });
  }, [t?.id]); // eslint-disable-line react-hooks/exhaustive-deps

  useEffect(() => {
    endRef.current?.scrollIntoView({ block: 'end' });
  }, [t?.messages.length]);

  if (!t) return <Navigate to={R.svTickets} replace />;
  const current = steps.findIndex((x) => x.s === t.status);

  const send = () => {
    if (!msg.trim()) return;
    addMessage(t.id, { from: 'student', text: msg.trim() });
    setMsg('');
  };

  return (
    <div className="m-shell">
      <div className="m-screen">
        <header className="m-header">
          <Link to={R.svTickets} aria-label="Quay lại" className="icon-btn">
            <Icon name="chevronLeft" size={22} />
          </Link>
          <h1 className="m-header__title">Phiếu #{t.id}</h1>
        </header>
        <main className="m-main">
          <div className="card">
            <div className="row gap-8">
              <StatusBadge status={t.status} />
              <span className="badge badge--muted badge--square">{t.anonymous ? 'Ẩn danh' : 'Có danh tính'}</span>
            </div>
            <p className="t-small t-muted">
              {t.type} · {t.group} · Gửi {t.createdAt}
            </p>
            <ol aria-label="Tiến trình phiếu" className="row gap-4" style={{ listStyle: 'none', margin: 0, padding: 0, alignItems: 'stretch' }}>
              {steps.map((x, i) => {
                const isNeed = x.s === 'Cần bổ sung';
                const reached = i <= current;
                const color = !reached ? 'var(--c-neutral)' : isNeed && i === current ? 'var(--c-danger)' : 'var(--c-primary)';
                return (
                  <li key={x.s} className="stack gap-6" style={{ flex: '1 1 0' }} aria-current={i === current ? 'step' : undefined}>
                    <span style={{ height: 4, borderRadius: 2, background: color }} />
                    <span className="t-small" style={{ color: reached ? color : 'var(--c-muted)' }}>
                      {x.label}
                    </span>
                  </li>
                );
              })}
            </ol>
          </div>
          {t.status === 'Cần bổ sung' && (
            <div className="panel panel--danger" style={{ borderRadius: 16, padding: 16, gap: 6 }}>
              <div className="row gap-8">
                <Icon name="alert" size={18} color="var(--c-danger)" />
                <p className="t-title t-danger">Cán bộ cần bạn bổ sung</p>
              </div>
              <p className="t-small">
                Trả lời trong hộp thư bên dưới.{t.anonymous ? ' Danh tính của bạn vẫn được ẩn.' : ''}
              </p>
            </div>
          )}
          <p className="t-h2">Hộp thư</p>
          {t.messages.map((m) => {
            const me = m.from === 'student';
            return (
              <div key={m.id} className="stack gap-4" style={{ alignItems: me ? 'flex-end' : 'flex-start' }}>
                <div className={`bubble ${m.from === 'system' ? 'bubble--system' : me ? 'bubble--me' : 'bubble--staff'}`} style={{ maxWidth: 280 }}>
                  {!me && (
                    <p className="bubble__meta" style={m.from === 'staff' ? { color: 'var(--c-primary)' } : undefined}>
                      {m.from === 'system' ? 'Xác nhận tự động' : 'Cán bộ phụ trách'}
                    </p>
                  )}
                  <p>{m.text}</p>
                </div>
                <p className="t-small t-muted">{me ? `Bạn · ${m.time}` : m.time}</p>
              </div>
            );
          })}
          <div ref={endRef} />
        </main>
        <form
          className="row gap-8"
          style={{ flex: '0 0 auto', padding: '10px 16px 22px', borderTop: '1px solid var(--c-border)', background: '#fff' }}
          onSubmit={(e) => {
            e.preventDefault();
            send();
          }}
        >
          <label className="icon-btn" aria-label="Đính kèm tệp" style={{ position: 'relative' }}>
            <Icon name="paperclip" size={20} color="var(--c-muted)" />
            <input type="file" style={{ position: 'absolute', width: 1, height: 1, opacity: 0 }} />
          </label>
          <label htmlFor="msg" className="sr-only">
            Nhập tin nhắn
          </label>
          <input
            id="msg"
            placeholder="Nhập tin nhắn…"
            className="input grow"
            style={{ background: 'var(--c-bg)' }}
            value={msg}
            onChange={(e) => setMsg(e.target.value)}
          />
          <button type="submit" aria-label="Gửi" className="icon-btn icon-btn--primary">
            <Icon name="send" size={20} color="#fff" />
          </button>
        </form>
      </div>
    </div>
  );
}
