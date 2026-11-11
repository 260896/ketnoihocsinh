import { useState } from 'react';
import { Link, Navigate, useNavigate, useParams } from 'react-router-dom';
import { ChoiceCard, EmergencyCard, Note } from '@/components/ui/Bits';
import { Dropzone, TextAreaField, TextField, formatSize } from '@/components/ui/Form';
import { Icon } from '@/components/ui/Icon';
import { isOutOfHours, site, telHref } from '@/config/site';
import { useStore } from '@/context/AppStore';
import { issueGroups, UNKNOWN_GROUP } from '@/data/mock';
import type { RequestType } from '@/data/types';
import { MobileScreen, StepProgress } from '@/layouts/MobileScreen';
import { R } from '@/lib/routes';

/** /sv/gui-phieu – vào luồng gửi phiếu; ngoài giờ trực thì hiện SV18 trước. */
export function SendEntry() {
  return <Navigate to={isOutOfHours() ? R.svOutOfHours : R.svSendStep(1)} replace />;
}

function Footer({ back, next, nextLabel = 'Tiếp tục', disabled }: { back?: string; next: () => void; nextLabel?: string; disabled?: boolean }) {
  return (
    <>
      {back && (
        <Link to={back} className="btn btn--outline">
          Quay lại
        </Link>
      )}
      <button type="button" className="btn" onClick={next} disabled={disabled}>
        {nextLabel}
      </button>
    </>
  );
}

/* SV12 – B1 Loại yêu cầu */
const types: { v: RequestType; hint: string }[] = [
  { v: 'Phản ánh', hint: 'Báo về một sự việc đã xảy ra' },
  { v: 'Đề nghị hỗ trợ', hint: 'Bạn hoặc người khác cần được giúp đỡ' },
  { v: 'Góp ý', hint: 'Ý kiến về hoạt động, nội dung học, website' },
];

export function SendStep1() {
  const { draft, setDraft } = useStore();
  const nav = useNavigate();
  return (
    <MobileScreen
      title="Gửi phiếu"
      back={R.svHome}
      tab="Gửi phiếu"
      footer={<Footer next={() => nav(R.svSendStep(2))} disabled={!draft.type} />}
    >
      <StepProgress step={1} />
      <h2 className="t-h1">Bạn muốn gửi loại yêu cầu nào?</h2>
      {types.map((t) => (
        <ChoiceCard key={t.v} selected={draft.type === t.v} title={t.v} hint={t.hint} onSelect={() => setDraft({ type: t.v })} />
      ))}
      <EmergencyCard />
    </MobileScreen>
  );
}

/* SV13 – B2 Nhóm vấn đề */
export function SendStep2() {
  const { draft, setDraft } = useStore();
  const nav = useNavigate();
  if (!draft.type) return <Navigate to={R.svSendStep(1)} replace />;
  return (
    <MobileScreen
      title="Gửi phiếu"
      back={R.svSendStep(1)}
      footer={<Footer back={R.svSendStep(1)} next={() => nav(R.svSendStep(3))} disabled={!draft.group} />}
    >
      <StepProgress step={2} />
      <h2 className="t-h1">Vấn đề thuộc nhóm nào?</h2>
      <p className="t-small t-muted">Danh sách cố định theo kịch bản đã chốt với khách hàng.</p>
      {issueGroups.map((g) => (
        <ChoiceCard key={g} selected={draft.group === g} title={g} onSelect={() => setDraft({ group: g })} />
      ))}
      <ChoiceCard
        selected={draft.group === UNKNOWN_GROUP}
        title={UNKNOWN_GROUP}
        hint="Cán bộ sẽ phân loại giúp bạn"
        onSelect={() => setDraft({ group: UNKNOWN_GROUP })}
      />
    </MobileScreen>
  );
}

/* SV14 – B3 Cần hỗ trợ ngay */
export function SendStep3() {
  const { draft, setDraft } = useStore();
  const nav = useNavigate();
  if (!draft.group) return <Navigate to={R.svSendStep(2)} replace />;
  return (
    <MobileScreen
      title="Gửi phiếu"
      back={R.svSendStep(2)}
      footer={<Footer back={R.svSendStep(2)} next={() => nav(R.svSendStep(4))} disabled={draft.urgent === null} />}
    >
      <StepProgress step={3} />
      <h2 className="t-h1">Bạn có cần hỗ trợ ngay không?</h2>
      <ChoiceCard selected={draft.urgent === true} title="Có, tôi cần hỗ trợ ngay" onSelect={() => setDraft({ urgent: true })} />
      <ChoiceCard selected={draft.urgent === false} title="Không, có thể chờ phản hồi" onSelect={() => setDraft({ urgent: false })} />
      {draft.urgent === true && (
        <div className="panel panel--danger-bordered" style={{ borderRadius: 16, padding: 18, gap: 12 }} role="alert">
          <div className="row gap-10">
            <span className="icon-circle" style={{ width: 40, height: 40, background: 'var(--c-danger)' }}>
              <Icon name="phone" size={20} color="#fff" />
            </span>
            <p className="t-h2 t-danger">Gọi ngay để được hỗ trợ</p>
          </div>
          <p>Nếu bạn đang gặp nguy hiểm, đừng chờ phiếu được xử lý.</p>
          <a href={telHref} className="btn btn--danger btn--block">
            Gọi {site.hotline}
          </a>
          <p className="t-small t-muted">
            Bạn vẫn có thể gửi phiếu. Phiếu sẽ được đánh dấu khẩn và gửi email ngay cho cán bộ trực.
          </p>
        </div>
      )}
    </MobileScreen>
  );
}

/* SV15 – B4 Mô tả */
export function SendStep4() {
  const { draft, setDraft } = useStore();
  const nav = useNavigate();
  if (draft.urgent === null) return <Navigate to={R.svSendStep(3)} replace />;
  return (
    <MobileScreen
      title="Gửi phiếu"
      back={R.svSendStep(3)}
      footer={<Footer back={R.svSendStep(3)} next={() => nav(R.svSendStep(5))} nextLabel="Xem lại" disabled={!draft.description.trim()} />}
    >
      <StepProgress step={4} />
      <h2 className="t-h1">Mô tả sự việc</h2>
      <TextAreaField
        label="Mô tả *"
        placeholder="Chuyện gì đã xảy ra? Ai liên quan? (không bắt buộc ghi tên)"
        value={draft.description}
        onChange={(e) => setDraft({ description: e.target.value })}
      />
      <TextField
        label="Thời gian"
        type="datetime-local"
        value={draft.occurredAt}
        onChange={(e) => setDraft({ occurredAt: e.target.value })}
      />
      <TextField
        label="Địa điểm"
        placeholder="Ví dụ: khu B, nhóm chat lớp…"
        value={draft.location}
        onChange={(e) => setDraft({ location: e.target.value })}
      />
      <p className="t-small t-strong">Tệp đính kèm</p>
      <Dropzone
        title="Chọn tệp để tải lên"
        hint={`${site.fileTypesLabel} · tối đa ${site.maxFileSizeLabel}/tệp`}
        iconSize={24}
        multiple
        onFiles={(fs) => setDraft({ files: [...draft.files, ...fs.map((f) => ({ name: f.name, size: formatSize(f.size) }))] })}
      />
      {draft.files.length > 0 && (
        <div className="card" style={{ borderRadius: 12, padding: '8px 12px' }}>
          {draft.files.map((f, i) => (
            <div key={`${f.name}-${i}`} className="row gap-12">
              <Icon name="file" size={20} color="var(--c-primary)" />
              <span className="grow">
                <span className="t-small t-strong" style={{ display: 'block', wordBreak: 'break-all' }}>
                  {f.name}
                </span>
                <span className="t-small t-muted">{f.size} · Đã tải lên</span>
              </span>
              <button
                type="button"
                aria-label={`Xóa tệp ${f.name}`}
                className="icon-btn"
                onClick={() => setDraft({ files: draft.files.filter((_, j) => j !== i) })}
              >
                <Icon name="x" size={18} color="var(--c-muted)" />
              </button>
            </div>
          ))}
        </div>
      )}
    </MobileScreen>
  );
}

/* SV16 – B5 Xem lại & gửi */
export function SendStep5() {
  const { draft, setDraft, submitDraft } = useStore();
  const nav = useNavigate();
  if (!draft.description.trim()) return <Navigate to={R.svSendStep(4)} replace />;

  const rows: [string, string][] = [
    ['Loại yêu cầu', draft.type ?? '—'],
    ['Nhóm vấn đề', draft.group ?? '—'],
    ['Cần hỗ trợ ngay', draft.urgent ? 'Có – phiếu khẩn' : 'Không'],
    ['Mô tả', draft.description],
    ['Thời gian · Địa điểm', `${draft.occurredAt ? new Date(draft.occurredAt).toLocaleString('vi-VN') : '—'} · ${draft.location || '—'}`],
    ['Tệp', draft.files.length ? `${draft.files.length} tệp` : 'Không có'],
  ];

  return (
    <MobileScreen
      title="Gửi phiếu"
      back={R.svSendStep(4)}
      footer={
        <Footer
          back={R.svSendStep(4)}
          nextLabel="Xác nhận gửi"
          next={() => {
            const t = submitDraft();
            nav(R.svSendDone(t.id), { replace: true });
          }}
        />
      }
    >
      <StepProgress step={5} />
      <h2 className="t-h1">Xem lại trước khi gửi</h2>
      <div className="card" style={{ gap: 12 }}>
        {rows.map(([k, v]) => (
          <div key={k} className="row gap-12 items-start">
            <p className="t-small t-muted" style={{ flex: '0 0 120px' }}>
              {k}
            </p>
            <p className="t-small t-strong" style={{ whiteSpace: 'pre-wrap', wordBreak: 'break-word', minWidth: 0 }}>
              {v}
            </p>
          </div>
        ))}
        <Link to={R.svSendStep(4)} className="link link--lg">
          Sửa thông tin
        </Link>
      </div>
      <p className="t-h2">Gửi với danh tính?</p>
      <ChoiceCard
        selected={draft.anonymous}
        title="Ẩn danh"
        hint="Cán bộ xử lý không thấy tên và email của bạn"
        onSelect={() => setDraft({ anonymous: true })}
      />
      <ChoiceCard
        selected={!draft.anonymous}
        title="Có danh tính"
        hint="Cán bộ thấy tên hiển thị và email để liên hệ"
        onSelect={() => setDraft({ anonymous: false })}
      />
      <div className="panel" style={{ borderRadius: 16, padding: 16 }}>
        <p className="t-title t-primary">Ai xem được phiếu này?</p>
        <Note icon="users">Cán bộ phụ trách nhóm vấn đề đọc nội dung và trả lời bạn.</Note>
        <Note icon="lock">Chỉ người được chỉ định mới mở được danh tính. Mỗi lần mở đều ghi nhật ký.</Note>
        <Note icon="eyeOff">Ẩn danh là ẩn khi hiển thị. Hệ thống vẫn lưu liên kết với tài khoản ở nơi riêng.</Note>
        <Note icon="file">Tệp gốc (vị trí GPS, thiết bị, thời điểm chụp) chỉ cán bộ có thẩm quyền xem để xác minh.</Note>
      </div>
    </MobileScreen>
  );
}

/* SV17 – Gửi thành công */
export function SendDone() {
  const { id = '' } = useParams();
  const { getTicket, showToast } = useStore();
  const t = getTicket(id);
  const [copied, setCopied] = useState(false);
  if (!t) return <Navigate to={R.svTickets} replace />;

  return (
    <MobileScreen title="Đã gửi phiếu" tab="Gửi phiếu">
      <div className="stack gap-14 items-center" style={{ paddingTop: 24 }}>
        <span className="icon-circle" style={{ width: 80, height: 80, background: 'var(--c-primary)' }}>
          <Icon name="check" size={40} color="#fff" />
        </span>
        <h2 className="t-h1 t-center">Phiếu đã được gửi</h2>
        <div className="panel panel--info self-stretch" style={{ borderRadius: 16, padding: 16 }}>
          <div className="row gap-12">
            <span className="grow">
              <span className="t-small t-muted" style={{ display: 'block' }}>
                Mã phiếu
              </span>
              <span style={{ fontSize: 22, fontWeight: 700, color: 'var(--c-primary)' }}>#{t.id}</span>
            </span>
            <button
              type="button"
              aria-label="Sao chép mã phiếu"
              className="icon-btn"
              onClick={() => {
                navigator.clipboard?.writeText(t.id);
                setCopied(true);
                showToast('Đã sao chép mã phiếu');
              }}
            >
              <Icon name={copied ? 'check' : 'copy'} size={20} color="var(--c-primary)" />
            </button>
          </div>
        </div>
        <div className="card self-stretch" style={{ gap: 6, alignItems: 'flex-start' }}>
          <span className="badge badge--muted badge--square">Xác nhận tự động</span>
          <p>{t.messages[0]?.text}</p>
        </div>
        <Note icon="clock" color="var(--c-muted)">
          <span className="t-muted">
            Ngoài giờ trực, phiếu được xử lý từ {site.dutyStartLabel}. Bạn nhận email khi phiếu có cập nhật (không kèm nội
            dung).
          </span>
        </Note>
        <Link to={R.svTicket(t.id)} className="btn btn--block">
          Xem phiếu
        </Link>
        <Link to={R.svHome} className="btn btn--ghost btn--block">
          Về trang chủ
        </Link>
      </div>
    </MobileScreen>
  );
}

/* SV18 – Ngoài giờ trực */
export function OutOfHours() {
  return (
    <MobileScreen title="Gửi phiếu" back={R.svHome} tab="Gửi phiếu">
      <div className="stack gap-10" style={{ background: 'var(--c-primary-dark)', borderRadius: 16, padding: 18 }}>
        <div className="row gap-10">
          <Icon name="moon" size={22} color="#fff" />
          <p className="t-h2" style={{ color: '#fff' }}>
            Hiện đang ngoài giờ trực
          </p>
        </div>
        <p style={{ color: '#fff' }}>
          Giờ trực: {site.dutyHoursLabel}. Phiếu gửi lúc này sẽ được tiếp nhận từ {site.dutyStartLabel}.
        </p>
      </div>
      <div className="panel panel--danger-bordered" style={{ borderRadius: 16, padding: 18 }}>
        <p className="t-h2 t-danger">Cần hỗ trợ ngay?</p>
        <p>Đừng chờ đến giờ trực. Gọi số hỗ trợ khẩn cấp:</p>
        <a href={telHref} className="btn btn--danger btn--block">
          Gọi {site.hotline}
        </a>
      </div>
      <p className="t-muted">Bạn vẫn có thể gửi phiếu bây giờ.</p>
      <Link to={R.svSendStep(1)} className="btn btn--block">
        Tiếp tục gửi phiếu
      </Link>
    </MobileScreen>
  );
}
