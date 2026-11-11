import { useMemo, useState } from 'react';
import { SelectField, TextField } from '@/components/ui/Form';
import { useStore } from '@/context/AppStore';
import type { AuditAction, AuditEntry } from '@/data/types';
import { AdminLayout } from '@/layouts/AdminLayout';

const ALL = 'Tất cả';
const sensitive: AuditAction[] = ['Mở danh tính', 'Tải tệp gốc'];

function exportCsv(rows: AuditEntry[]) {
  const esc = (v: string) => `"${v.replace(/"/g, '""')}"`;
  const head = ['Thời gian', 'Người thực hiện', 'Hành động', 'Phiếu', 'Chi tiết'];
  const body = rows.map((r) => [r.time, r.actor, r.action, r.ticketId ? `#${r.ticketId}` : '—', r.detail].map(esc).join(','));
  const blob = new Blob(['﻿' + [head.map(esc).join(','), ...body].join('\n')], { type: 'text/csv;charset=utf-8' });
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = `nhat-ky-thao-tac-${new Date().toISOString().slice(0, 10)}.csv`;
  a.click();
  URL.revokeObjectURL(a.href);
}

/* QT09 – Nhật ký thao tác */
export default function AdminAudit() {
  const { audit } = useStore();
  const [from, setFrom] = useState('');
  const [to, setTo] = useState('');
  const [action, setAction] = useState(ALL);
  const [actor, setActor] = useState(ALL);
  const [ticket, setTicket] = useState('');

  const actions = useMemo(() => [ALL, ...new Set(audit.map((a) => a.action))], [audit]);
  const actors = useMemo(() => [ALL, ...new Set(audit.map((a) => a.actor))], [audit]);

  const rows = audit.filter(
    (r) =>
      (action === ALL || r.action === action) &&
      (actor === ALL || r.actor === actor) &&
      (!ticket.trim() || (r.ticketId ?? '').toLowerCase().includes(ticket.trim().replace(/^#/, '').toLowerCase())),
  );

  return (
    <AdminLayout
      section="Nhật ký"
      title="Nhật ký thao tác"
      subtitle="Ghi lại phân loại, phân công, phản hồi, mở danh tính và tải tệp gốc."
      actions={
        <button type="button" className="btn btn--outline" onClick={() => exportCsv(rows)}>
          Xuất CSV
        </button>
      }
    >
      <div className="card card--admin" style={{ padding: 16 }}>
        <div className="filter-grid" style={{ gridTemplateColumns: 'repeat(auto-fit, minmax(170px, 1fr))' }}>
          {/* TODO: lọc theo ngày khi nhật ký có timestamp thật từ API */}
          <TextField label="Từ ngày" type="date" value={from} onChange={(e) => setFrom(e.target.value)} />
          <TextField label="Đến ngày" type="date" value={to} onChange={(e) => setTo(e.target.value)} />
          <SelectField label="Hành động" options={actions} value={action} onChange={(e) => setAction(e.target.value)} />
          <SelectField label="Người thực hiện" options={actors} value={actor} onChange={(e) => setActor(e.target.value)} />
          <TextField label="Mã phiếu" placeholder="#…" value={ticket} onChange={(e) => setTicket(e.target.value)} />
        </div>
      </div>

      <div className="table-wrap">
        <table className="table">
          <thead>
            <tr>
              <th scope="col" style={{ width: 150 }}>Thời gian</th>
              <th scope="col" style={{ width: 160 }}>Người thực hiện</th>
              <th scope="col" style={{ width: 150 }}>Hành động</th>
              <th scope="col" style={{ width: 120 }}>Phiếu</th>
              <th scope="col">Chi tiết</th>
            </tr>
          </thead>
          <tbody>
            {rows.map((r) => (
              <tr key={r.id}>
                <td>{r.time}</td>
                <td>{r.actor}</td>
                <td>
                  <span className={`badge ${sensitive.includes(r.action) ? 'badge--danger' : 'badge--info'}`}>{r.action}</span>
                </td>
                <td>{r.ticketId ? `#${r.ticketId}` : '—'}</td>
                <td>{r.detail}</td>
              </tr>
            ))}
            {rows.length === 0 && (
              <tr>
                <td colSpan={5} className="t-muted t-center" style={{ fontWeight: 400 }}>
                  Không có bản ghi phù hợp.
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>
    </AdminLayout>
  );
}
