import { useMemo, useState } from 'react';
import { Link, Navigate, useMatch, useNavigate, useParams } from 'react-router-dom';
import { PriorityBadge, StatusBadge } from '@/components/ui/Bits';
import { SelectField, TextAreaField, TextField } from '@/components/ui/Form';
import { Icon } from '@/components/ui/Icon';
import { site } from '@/config/site';
import { useStore } from '@/context/AppStore';
import { issueGroups, replyTemplates, revealBases, staffNames, UNKNOWN_GROUP } from '@/data/mock';
import type { Priority, Ticket, TicketStatus } from '@/data/types';
import { AdminLayout } from '@/layouts/AdminLayout';
import { R } from '@/lib/routes';

const ALL = 'Tất cả';
const PAGE_SIZE = 7;
const statuses: TicketStatus[] = ['Đã tiếp nhận', 'Cần bổ sung', 'Đang xem xét', 'Đã có phản hồi'];
const priorities: Priority[] = ['Khẩn', 'Cao', 'Thường'];

type Quick = 'all' | 'queue' | 'urgent' | 'overdue' | 'mine';
const quickFilters: { k: Quick; label: string; danger?: boolean; test: (t: Ticket) => boolean }[] = [
  { k: 'all', label: 'Tất cả', test: () => true },
  { k: 'queue', label: 'Hàng chờ phân loại', test: (t) => t.assignee === null },
  { k: 'urgent', label: 'Khẩn', danger: true, test: (t) => t.priority === 'Khẩn' },
  { k: 'overdue', label: 'Quá hạn', danger: true, test: (t) => !!t.deadlineAlert?.startsWith('Quá hạn') },
  { k: 'mine', label: 'Của tôi', test: (t) => !!t.mine },
];

/* QT02 – Danh sách phiếu */
export function AdminTickets() {
  const { tickets } = useStore();
  const [quick, setQuick] = useState<Quick>('all');
  const [q, setQ] = useState('');
  const [f, setF] = useState({ group: ALL, priority: ALL, status: ALL, deadline: ALL, assignee: ALL });
  const [page, setPage] = useState(0);
  const setFilter = (k: keyof typeof f) => (e: React.ChangeEvent<HTMLSelectElement>) => {
    setF((x) => ({ ...x, [k]: e.target.value }));
    setPage(0);
  };

  const groups = useMemo(() => [ALL, ...new Set(tickets.map((t) => t.group))], [tickets]);
  const assignees = useMemo(() => [ALL, 'Hàng chờ phân loại', ...new Set(tickets.map((t) => t.assignee).filter(Boolean) as string[])], [tickets]);

  const filtered = tickets.filter((t) => {
    const qf = quickFilters.find((x) => x.k === quick)!;
    if (!qf.test(t)) return false;
    const s = q.trim().toLowerCase();
    if (s && !`${t.id} ${t.type} ${t.group} ${t.description}`.toLowerCase().includes(s)) return false;
    if (f.group !== ALL && t.group !== f.group) return false;
    if (f.priority !== ALL && t.priority !== f.priority) return false;
    if (f.status !== ALL && t.status !== f.status) return false;
    if (f.deadline === 'Quá hạn' && !t.deadlineAlert?.startsWith('Quá hạn')) return false;
    if (f.deadline === 'Sắp đến hạn' && !t.deadlineAlert?.startsWith('Còn')) return false;
    if (f.assignee === 'Hàng chờ phân loại' && t.assignee !== null) return false;
    if (f.assignee !== ALL && f.assignee !== 'Hàng chờ phân loại' && t.assignee !== f.assignee) return false;
    return true;
  });
  const pages = Math.max(1, Math.ceil(filtered.length / PAGE_SIZE));
  const shown = filtered.slice(page * PAGE_SIZE, page * PAGE_SIZE + PAGE_SIZE);

  return (
    <AdminLayout section="Phiếu phản ánh" title="Phiếu phản ánh" subtitle="Theo dõi, phân loại và phản hồi phiếu của sinh viên">
      <div className="row wrap gap-8">
        {quickFilters.map((x) => (
          <button
            key={x.k}
            type="button"
            className={`chip${x.danger ? ' chip--danger' : ''}${quick === x.k ? ' chip--active' : ''}`}
            aria-pressed={quick === x.k}
            onClick={() => {
              setQuick(x.k);
              setPage(0);
            }}
          >
            {x.label} · {tickets.filter(x.test).length}
          </button>
        ))}
      </div>

      <div className="card card--admin" style={{ padding: 16 }}>
        <div className="filter-grid">
          <TextField label="Tìm kiếm" placeholder="Mã phiếu, từ khóa" value={q} onChange={(e) => setQ(e.target.value)} />
          <SelectField label="Nhóm vấn đề" options={groups} value={f.group} onChange={setFilter('group')} />
          <SelectField label="Mức ưu tiên" options={[ALL, ...priorities]} value={f.priority} onChange={setFilter('priority')} />
          <SelectField label="Trạng thái" options={[ALL, ...statuses]} value={f.status} onChange={setFilter('status')} />
          <SelectField label="Hạn xử lý" options={[ALL, 'Sắp đến hạn', 'Quá hạn']} value={f.deadline} onChange={setFilter('deadline')} />
          <SelectField label="Người phụ trách" options={assignees} value={f.assignee} onChange={setFilter('assignee')} />
        </div>
      </div>

      <div className="table-wrap">
        <table className="table">
          <thead>
            <tr>
              <th scope="col" style={{ width: 120 }}>Mã phiếu</th>
              <th scope="col">Loại · Nhóm vấn đề</th>
              <th scope="col" style={{ width: 90 }}>Ưu tiên</th>
              <th scope="col" style={{ width: 140 }}>Trạng thái</th>
              <th scope="col" style={{ width: 130 }}>Hạn xử lý</th>
              <th scope="col" style={{ width: 170 }}>Phụ trách</th>
              <th scope="col" style={{ width: 110 }}>Danh tính</th>
            </tr>
          </thead>
          <tbody>
            {shown.map((t) => (
              <tr key={t.id} className={t.deadlineAlert ? 'is-alert' : undefined}>
                <td>
                  <Link to={R.qtTicket(t.id)} style={{ textDecoration: 'none' }}>
                    #{t.id}
                  </Link>
                </td>
                <td>
                  {t.type} · {t.group === UNKNOWN_GROUP ? 'Chưa rõ nhóm' : t.group}
                </td>
                <td>
                  <PriorityBadge priority={t.priority} />
                </td>
                <td>
                  <StatusBadge status={t.status} />
                </td>
                <td>{t.deadlineAlert ? <span className="t-danger t-strong">{t.deadlineAlert}</span> : t.deadline}</td>
                <td>{t.assignee ?? <span className="t-muted">Hàng chờ phân loại</span>}</td>
                <td>{t.anonymous ? 'Ẩn danh' : 'Có danh tính'}</td>
              </tr>
            ))}
            {shown.length === 0 && (
              <tr>
                <td colSpan={7} className="t-muted t-center" style={{ fontWeight: 400 }}>
                  Không có phiếu phù hợp bộ lọc.
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>

      <div className="row gap-8">
        <p className="t-small t-muted grow">
          Hiển thị {filtered.length ? page * PAGE_SIZE + 1 : 0}–{Math.min(filtered.length, (page + 1) * PAGE_SIZE)} / {filtered.length} phiếu
        </p>
        <button type="button" className="btn btn--outline" disabled={page === 0} onClick={() => setPage((p) => p - 1)}>
          Trang trước
        </button>
        <button type="button" className="btn btn--outline" disabled={page >= pages - 1} onClick={() => setPage((p) => p + 1)}>
          Trang sau
        </button>
      </div>
    </AdminLayout>
  );
}

/* QT03 – Xử lý phiếu (+ QT04 – Form mở danh tính dạng hộp thoại) */
export function AdminTicketDetail() {
  const { id = '' } = useParams();
  const revealOpen = !!useMatch('/qt/phieu/:id/mo-danh-tinh');
  const { getTicket, updateTicket, addMessage, log, showToast } = useStore();
  const t = getTicket(id);
  const [reply, setReply] = useState('');
  const [tpl, setTpl] = useState('');
  const [form, setForm] = useState(() => ({
    group: t?.group ?? UNKNOWN_GROUP,
    priority: t?.priority ?? 'Thường',
    status: t?.status ?? 'Đã tiếp nhận',
    assignee: t?.assignee ?? 'Hàng chờ phân loại',
    deadline: t?.deadline ?? '',
  }));

  if (!t) return <Navigate to={R.qtTickets} replace />;

  const setField = (k: keyof typeof form) => (e: React.ChangeEvent<HTMLSelectElement | HTMLInputElement>) =>
    setForm((x) => ({ ...x, [k]: e.target.value }));

  const save = () => {
    const changes: string[] = [];
    if (form.status !== t.status) changes.push(`đổi trạng thái → ${form.status}`);
    if (form.priority !== t.priority) changes.push(`đổi ưu tiên ${t.priority} → ${form.priority}`);
    if (form.group !== t.group) changes.push(`phân loại → ${form.group}`);
    const assignee = form.assignee === 'Hàng chờ phân loại' ? null : form.assignee;
    if (assignee !== t.assignee) changes.push(`phân công → ${form.assignee}`);
    updateTicket(t.id, {
      group: form.group,
      priority: form.priority as Priority,
      status: form.status as TicketStatus,
      assignee,
      deadline: form.deadline,
      history: changes.length ? [{ time: 'Vừa xong', text: `[Tên cán bộ] ${changes.join(', ')}` }, ...t.history] : t.history,
    });
    if (form.group !== t.group) log({ actor: '[Tên cán bộ]', action: 'Phân loại', ticketId: t.id, detail: `${t.group} → ${form.group}` });
    if (assignee !== t.assignee) log({ actor: '[Tên cán bộ]', action: 'Phân công', ticketId: t.id, detail: `→ ${form.assignee}` });
    if (form.priority !== t.priority) log({ actor: '[Tên cán bộ]', action: 'Ưu tiên', ticketId: t.id, detail: `${t.priority} → ${form.priority}` });
    showToast('Đã lưu thay đổi');
  };

  const sendReply = () => {
    if (!reply.trim()) return;
    addMessage(t.id, { from: 'staff', author: '[Tên cán bộ]', text: reply.trim() });
    updateTicket(t.id, { hasUpdate: true });
    log({ actor: '[Tên cán bộ]', action: 'Phản hồi', ticketId: t.id, detail: 'Gửi tin nhắn trong hộp thư' });
    setReply('');
    setTpl('');
    showToast('Đã gửi phản hồi');
  };

  const info: [string, string][] = [
    ['Mô tả', t.description],
    ['Thời gian', t.occurredAt],
    ['Địa điểm', t.location],
    ['Cần hỗ trợ ngay', t.urgent ? 'Có' : 'Không'],
  ];

  return (
    <AdminLayout
      section="Phiếu phản ánh"
      title={`Phiếu #${t.id}`}
      subtitle={`${t.type} · Gửi ${t.createdAt} · ${t.anonymous ? 'Ẩn danh' : 'Có danh tính'}`}
      actions={
        <button type="button" className="btn btn--outline" onClick={() => document.getElementById('reply-tpl')?.focus()}>
          Dùng mẫu trả lời
        </button>
      }
    >
      <div className="a-cols">
        <div className="a-col-main">
          <div className="card card--admin">
            <div className="row wrap gap-8">
              <StatusBadge status={t.status} />
              <PriorityBadge priority={t.priority} />
              {t.anonymous && (
                <span className="row gap-6 badge badge--muted badge--square" style={{ padding: '4px 8px' }}>
                  <Icon name="eyeOff" size={14} color="var(--c-muted)" />
                  Ẩn danh – danh tính bị ẩn
                </span>
              )}
            </div>
            {info.map(([k, v]) => (
              <div key={k} className="row gap-16 items-start">
                <p className="t-small t-muted" style={{ flex: '0 0 140px' }}>
                  {k}
                </p>
                <p style={{ whiteSpace: 'pre-wrap' }}>{v}</p>
              </div>
            ))}
            {t.attachments.map((a) => (
              <div key={a.name} className="panel" style={{ padding: 12 }}>
                <div className="row wrap gap-12">
                  <Icon name="file" size={20} color="var(--c-primary)" />
                  <div style={{ flex: '1 1 200px' }}>
                    <p className="t-small t-strong">
                      {a.name} · {a.size}
                    </p>
                    <p className="t-small t-muted">Sinh viên thấy bản đã xóa siêu dữ liệu</p>
                  </div>
                  <button
                    type="button"
                    className="btn btn--outline"
                    onClick={() => {
                      log({ actor: '[Tên cán bộ]', action: 'Tải tệp gốc', ticketId: t.id, detail: `${a.name} (bản gốc kèm siêu dữ liệu)` });
                      showToast('Đã ghi nhật ký xem bản gốc');
                    }}
                  >
                    Xem bản gốc (ghi nhật ký)
                  </button>
                </div>
              </div>
            ))}
          </div>

          <div className="card card--admin" style={{ gap: 12 }}>
            <p className="t-h2">Hộp thư theo mã phiếu</p>
            {t.messages.map((m) => {
              const staff = m.from === 'staff';
              return (
                <div key={m.id} className="row" style={{ justifyContent: staff ? 'flex-end' : 'flex-start' }}>
                  <div className={`bubble ${m.from === 'system' ? 'bubble--system' : staff ? 'bubble--me' : 'bubble--staff'}`}>
                    <p className="bubble__meta">
                      {m.from === 'system'
                        ? `Xác nhận tự động · ${m.time}`
                        : staff
                          ? `Bạn · ${m.author ?? ''} · ${m.time}`
                          : `Sinh viên${t.anonymous ? ' (ẩn danh)' : ''} · ${m.time}`}
                    </p>
                    <p>{m.text}</p>
                  </div>
                </div>
              );
            })}
            <TextAreaField
              label="Trả lời sinh viên"
              placeholder="Nhập phản hồi hoặc chọn mẫu trả lời…"
              value={reply}
              onChange={(e) => setReply(e.target.value)}
            />
            <SelectField
              id="reply-tpl"
              label="Mẫu trả lời"
              value={tpl}
              options={[{ value: '', label: 'Chọn mẫu trả lời' }, ...replyTemplates.map((x) => ({ value: x.id, label: x.name }))]}
              onChange={(e) => {
                setTpl(e.target.value);
                const found = replyTemplates.find((x) => x.id === e.target.value);
                if (found) setReply(found.body);
              }}
            />
            <div className="row wrap gap-12">
              <p className="t-small t-muted" style={{ flex: '1 1 200px' }}>
                Sinh viên chỉ nhận email “phiếu có cập nhật”, không kèm nội dung.
              </p>
              <button type="button" className="btn" onClick={sendReply} disabled={!reply.trim()}>
                Gửi phản hồi
              </button>
            </div>
          </div>
        </div>

        <div className="a-col-side">
          <div className="card card--admin">
            <p className="t-h2">Phân loại &amp; phân công</p>
            <SelectField label="Nhóm vấn đề" options={[...issueGroups, UNKNOWN_GROUP, ...(issueGroups.includes(t.group) || t.group === UNKNOWN_GROUP ? [] : [t.group])]} value={form.group} onChange={setField('group')} />
            <SelectField label="Mức ưu tiên" options={priorities} value={form.priority} onChange={setField('priority')} />
            <SelectField label="Trạng thái" options={statuses} value={form.status} onChange={setField('status')} />
            <SelectField
              label="Người phụ trách"
              options={['Hàng chờ phân loại', ...staffNames, ...(t.assignee && !staffNames.includes(t.assignee) ? [t.assignee] : [])]}
              value={form.assignee}
              onChange={setField('assignee')}
            />
            <TextField label="Hạn xử lý" value={form.deadline} onChange={setField('deadline')} placeholder="[Ngày giờ]" />
            <button type="button" className="btn btn--block" onClick={save}>
              Lưu thay đổi
            </button>
          </div>

          {t.anonymous && (
            <div className="panel panel--danger" style={{ borderRadius: 14, padding: 20 }}>
              <div className="row gap-8">
                <Icon name="lock" size={18} color="var(--c-danger)" />
                <p className="t-title t-danger">Danh tính người gửi</p>
              </div>
              <p className="t-small">
                Đang ẩn. Chỉ người được chỉ định mới mở được, theo quy tắc {site.unit} ban hành. Mỗi lần mở đều ghi nhật ký.
              </p>
              <Link to={R.qtReveal(t.id)} className="btn btn--danger-outline btn--block">
                Yêu cầu mở danh tính
              </Link>
            </div>
          )}

          <div className="card card--admin" style={{ gap: 10 }}>
            <p className="t-h2">Nhật ký phiếu</p>
            {t.history.length === 0 && <p className="t-small t-muted">Chưa có thao tác.</p>}
            {t.history.map((h, i) => (
              <div key={i} className="row gap-10 items-start t-small">
                <p className="t-muted" style={{ flex: '0 0 auto' }}>
                  {h.time}
                </p>
                <p>{h.text}</p>
              </div>
            ))}
          </div>
        </div>
      </div>

      {revealOpen && <RevealIdentityDialog ticket={t} />}
    </AdminLayout>
  );
}

/* QT04 – Form mở danh tính */
function RevealIdentityDialog({ ticket }: { ticket: Ticket }) {
  const nav = useNavigate();
  const { log } = useStore();
  const [basis, setBasis] = useState('');
  const [reason, setReason] = useState('');
  const [confirm, setConfirm] = useState(false);
  const close = () => nav(R.qtTicket(ticket.id));
  const valid = basis && reason.trim() && confirm;

  return (
    <div className="modal-backdrop" onClick={(e) => e.target === e.currentTarget && close()} onKeyDown={(e) => e.key === 'Escape' && close()}>
      <form
        role="dialog"
        aria-modal="true"
        aria-labelledby="dlg"
        className="modal"
        onSubmit={(e) => {
          e.preventDefault();
          if (!valid) return;
          log({ actor: '[Tên cán bộ]', action: 'Mở danh tính', ticketId: ticket.id, detail: `Căn cứ: ${basis} · Lý do: ${reason.trim()}` });
          nav(R.qtAudit);
        }}
      >
        <div className="row gap-10">
          <span className="icon-circle" style={{ width: 40, height: 40, background: 'var(--c-danger-50)' }}>
            <Icon name="lock" size={20} color="var(--c-danger)" />
          </span>
          <h2 id="dlg" className="t-h2 grow">
            Mở danh tính phiếu #{ticket.id}
          </h2>
          <button type="button" aria-label="Đóng" className="icon-btn" onClick={close}>
            <Icon name="x" size={20} color="var(--c-muted)" />
          </button>
        </div>
        <p className="t-small t-muted">
          Chỉ dùng khi đúng trường hợp trong quy tắc mở danh tính. Thao tác này được ghi nhật ký kèm tên bạn, thời điểm và lý do.
        </p>
        <SelectField
          label="Căn cứ mở danh tính *"
          value={basis}
          onChange={(e) => setBasis(e.target.value)}
          options={[{ value: '', label: 'Chọn trường hợp theo quy tắc' }, ...revealBases]}
          autoFocus
        />
        <TextAreaField label="Lý do cụ thể *" placeholder="Mô tả vì sao cần mở danh tính" value={reason} onChange={(e) => setReason(e.target.value)} />
        <label className="note" style={{ cursor: 'pointer' }}>
          <input type="checkbox" className="checkbox" checked={confirm} onChange={(e) => setConfirm(e.target.checked)} />
          <span>Tôi là người được chỉ định và hiểu thao tác này được ghi nhật ký.</span>
        </label>
        <div className="row gap-12 justify-end">
          <button type="button" className="btn btn--outline" onClick={close}>
            Hủy
          </button>
          <button type="submit" className="btn btn--danger" disabled={!valid}>
            Mở danh tính
          </button>
        </div>
      </form>
    </div>
  );
}
