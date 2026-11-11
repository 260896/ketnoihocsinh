import { useState } from 'react';
import { SelectField, SwitchRow, TextField } from '@/components/ui/Form';
import { Icon } from '@/components/ui/Icon';
import { site } from '@/config/site';
import { useStore } from '@/context/AppStore';
import { replyTemplates, routing as initialRouting, staffNames } from '@/data/mock';
import { AdminLayout } from '@/layouts/AdminLayout';

const times = Array.from({ length: 48 }, (_, i) => `${String(Math.floor(i / 2)).padStart(2, '0')}:${i % 2 ? '30' : '00'}`);

/* QT07 – Cấu hình đầu mối & vận hành */
export default function AdminConfig() {
  const { showToast } = useStore();
  const [routing, setRouting] = useState(initialRouting);
  const [templates, setTemplates] = useState(replyTemplates);
  const [duty, setDuty] = useState({ start: site.duty.start, end: site.duty.end, days: 'Thứ 2 – Thứ 6', urgentAck: '[15] phút', firstReply: '[48] giờ' });
  const [fallback, setFallback] = useState(true);
  const [hotline, setHotline] = useState('');
  const [code, setCode] = useState('NK2026-XXXX');
  const [fileTypes, setFileTypes] = useState('JPG, PNG, PDF, MP4');
  const [maxSize, setMaxSize] = useState('[10] MB');

  const setRoute = (i: number, k: 'primary' | 'backup' | 'independent', v: string) =>
    setRouting((r) => r.map((row, j) => (j === i ? { ...row, [k]: v } : row)));

  const regenCode = () => {
    const s = Math.random().toString(36).slice(2, 6).toUpperCase();
    setCode(`NK${new Date().getFullYear()}-${s}`);
  };

  return (
    <AdminLayout
      section="Cấu hình đầu mối"
      title="Cấu hình đầu mối & vận hành"
      subtitle="Phiếu tự chuyển theo nhóm vấn đề. Phiếu chưa rõ nhóm vào hàng chờ phân loại."
      actions={
        <button type="button" className="btn" onClick={() => showToast('Đã lưu cấu hình')}>
          Lưu cấu hình
        </button>
      }
    >
      <div className="a-cols">
        <div className="a-col-main">
          <div className="card card--admin">
            <div>
              <p className="t-h2">Bảng đầu mối theo nhóm vấn đề</p>
              <p className="t-small t-muted">
                Đầu mối độc lập nhận phiếu khi phiếu liên quan đến chính đầu mối hoặc người dự phòng.
              </p>
            </div>
            <div className="table-wrap">
              <table className="table">
                <thead>
                  <tr>
                    <th scope="col" style={{ width: 160 }}>Nhóm vấn đề</th>
                    <th scope="col">Đầu mối</th>
                    <th scope="col">Người dự phòng</th>
                    <th scope="col">Đầu mối độc lập</th>
                  </tr>
                </thead>
                <tbody>
                  {routing.map((r, i) => (
                    <tr key={r.group}>
                      <td>{r.group}</td>
                      <td>
                        <SelectField
                          label={`Đầu mối – ${r.group}`}
                          hideLabel
                          options={i === routing.length - 1 ? ['Hàng chờ phân loại', ...staffNames] : staffNames}
                          value={r.primary}
                          onChange={(e) => setRoute(i, 'primary', e.target.value)}
                        />
                      </td>
                      <td>
                        <SelectField label={`Người dự phòng – ${r.group}`} hideLabel options={staffNames} value={r.backup} onChange={(e) => setRoute(i, 'backup', e.target.value)} />
                      </td>
                      <td>
                        <SelectField
                          label={`Đầu mối độc lập – ${r.group}`}
                          hideLabel
                          options={staffNames}
                          value={r.independent}
                          onChange={(e) => setRoute(i, 'independent', e.target.value)}
                        />
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>

          <div className="card card--admin">
            <p className="t-h2">Mẫu trả lời</p>
            {templates.map((t) => (
              <div key={t.id} className="panel" style={{ borderRadius: 10, padding: '6px 14px' }}>
                <div className="row gap-12">
                  <p className="t-small t-strong grow">{t.name}</p>
                  <button type="button" aria-label={`Sửa mẫu ${t.name}`} className="icon-btn" onClick={() => showToast('Trình soạn mẫu sẽ bổ sung sau')}>
                    <Icon name="edit" size={18} color="var(--c-primary)" />
                  </button>
                </div>
              </div>
            ))}
            <div>
              <button
                type="button"
                className="btn btn--outline"
                onClick={() => setTemplates((ts) => [...ts, { id: crypto.randomUUID(), name: `[Mẫu trả lời ${ts.length + 1}]`, body: '' }])}
              >
                Thêm mẫu trả lời
              </button>
            </div>
          </div>
        </div>

        <div className="a-col-side" style={{ flexBasis: 380 }}>
          <div className="card card--admin">
            <p className="t-h2">Giờ trực &amp; thời hạn</p>
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(120px, 1fr))', gap: 12, alignItems: 'end' }}>
              <SelectField label="Bắt đầu trực" options={times} value={duty.start} onChange={(e) => setDuty((d) => ({ ...d, start: e.target.value }))} />
              <SelectField label="Kết thúc trực" options={times} value={duty.end} onChange={(e) => setDuty((d) => ({ ...d, end: e.target.value }))} />
            </div>
            <SelectField label="Ngày trực" options={['Thứ 2 – Thứ 6', 'Thứ 2 – Thứ 7', 'Cả tuần']} value={duty.days} onChange={(e) => setDuty((d) => ({ ...d, days: e.target.value }))} />
            <SelectField label="Hạn xác nhận phiếu khẩn" options={['[15] phút', '[30] phút', '[60] phút']} value={duty.urgentAck} onChange={(e) => setDuty((d) => ({ ...d, urgentAck: e.target.value }))} />
            <SelectField
              label="Hạn phản hồi lần đầu (phiếu thường)"
              options={['[24] giờ', '[48] giờ', '[72] giờ']}
              value={duty.firstReply}
              onChange={(e) => setDuty((d) => ({ ...d, firstReply: e.target.value }))}
            />
            <SwitchRow title="Chuyển người dự phòng khi quá hạn" hint="Nếu chưa có ai xác nhận" checked={fallback} onChange={setFallback} />
            <TextField label="Số hỗ trợ khẩn cấp ngoài giờ *" placeholder={site.hotline} value={hotline} onChange={(e) => setHotline(e.target.value)} type="tel" />
          </div>

          <div className="card card--admin">
            <p className="t-h2">Mã tham gia &amp; tệp đính kèm</p>
            <div className="row gap-10" style={{ alignItems: 'flex-end' }}>
              <div className="grow">
                <TextField label="Mã tham gia hiện tại" value={code} onChange={(e) => setCode(e.target.value)} />
              </div>
              <button type="button" className="btn btn--outline" onClick={regenCode}>
                Đổi mã
              </button>
            </div>
            <SelectField label="Loại tệp cho phép" options={['JPG, PNG, PDF, MP4', 'JPG, PNG, PDF', 'JPG, PNG']} value={fileTypes} onChange={(e) => setFileTypes(e.target.value)} />
            <SelectField label="Dung lượng tối đa mỗi tệp" options={['[5] MB', '[10] MB', '[20] MB']} value={maxSize} onChange={(e) => setMaxSize(e.target.value)} />
          </div>
        </div>
      </div>
    </AdminLayout>
  );
}
