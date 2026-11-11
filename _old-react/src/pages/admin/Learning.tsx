import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { PublishBadge } from '@/components/ui/Bits';
import { Dropzone, SelectField, SwitchRow, TextAreaField, TextField } from '@/components/ui/Form';
import { Icon } from '@/components/ui/Icon';
import { site } from '@/config/site';
import { useStore } from '@/context/AppStore';
import { learningDocs, topics as initialTopics } from '@/data/mock';
import type { LearningDoc, PublishStatus, Topic } from '@/data/types';
import { AdminLayout } from '@/layouts/AdminLayout';
import { R } from '@/lib/routes';

/* QT05 – Học liệu */
export function AdminLearning() {
  const { showToast } = useStore();
  const [topics, setTopics] = useState<Topic[]>(initialTopics);
  const [active, setActive] = useState(topics[0].id);
  const [docs, setDocs] = useState<LearningDoc[]>(learningDocs);
  const [upload, setUpload] = useState({ name: '', topicId: topics[0].id, format: 'PDF' as LearningDoc['format'], file: '', link: '' });
  const topic = topics.find((t) => t.id === active)!;

  const setSituationStatus = (sid: string, status: PublishStatus) =>
    setTopics((ts) => ts.map((t) => (t.id !== active ? t : { ...t, situations: t.situations.map((s) => (s.id === sid ? { ...s, status } : s)) })));

  const setDocStatus = (id: string, status: PublishStatus) => setDocs((ds) => ds.map((d) => (d.id === id ? { ...d, status } : d)));

  const saveDoc = (status: PublishStatus) => {
    if (!upload.name.trim()) return showToast('Nhập tên tài liệu');
    setDocs((ds) => [...ds, { id: crypto.randomUUID(), topicId: upload.topicId, name: upload.name.trim(), format: upload.format, status }]);
    setUpload((u) => ({ ...u, name: '', file: '', link: '' }));
    showToast(status === 'Đã công bố' ? 'Đã công bố tài liệu' : 'Đã lưu nháp');
  };

  return (
    <AdminLayout
      section="Học liệu"
      title="Học liệu"
      subtitle="Chủ đề, tình huống và tài liệu. Sinh viên chỉ thấy bản đã công bố."
      actions={
        <>
          <button type="button" className="btn btn--outline" onClick={() => document.getElementById('doc-name')?.focus()}>
            Tải tài liệu
          </button>
          <Link to={R.qtNewSituation} className="btn">
            Thêm tình huống
          </Link>
        </>
      }
    >
      <div className="a-cols">
        <div className="a-col-main">
          <div className="row wrap gap-8">
            {topics.map((t) => (
              <button key={t.id} type="button" className={`chip${t.id === active ? ' chip--active' : ''}`} aria-pressed={t.id === active} onClick={() => setActive(t.id)}>
                {t.name}
              </button>
            ))}
            <button type="button" className="chip" onClick={() => showToast('Tính năng thêm chủ đề sẽ bổ sung sau')}>
              + Chủ đề mới
            </button>
          </div>

          <div className="table-wrap">
            <table className="table">
              <thead>
                <tr>
                  <th scope="col">Tình huống</th>
                  <th scope="col" style={{ width: 110 }}>Loại</th>
                  <th scope="col" style={{ width: 120 }}>Trạng thái</th>
                  <th scope="col" style={{ width: 110 }}>Cập nhật</th>
                  <th scope="col" style={{ width: 150 }}>
                    <span className="sr-only">Thao tác</span>
                  </th>
                </tr>
              </thead>
              <tbody>
                {topic.situations.map((s, i) => (
                  <tr key={s.id}>
                    <td>[Tình huống {i + 1}]</td>
                    <td>{s.kind}</td>
                    <td>
                      <PublishBadge status={s.status} />
                    </td>
                    <td>{s.updatedAt}</td>
                    <td>
                      <div className="table-actions">
                        <Link to={R.qtNewSituation} className="link">
                          Sửa
                        </Link>
                        {s.status === 'Nháp' && (
                          <button type="button" className="link" onClick={() => setSituationStatus(s.id, 'Đã công bố')}>
                            Công bố
                          </button>
                        )}
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          <p className="t-h2">Tài liệu</p>
          <div className="table-wrap">
            <table className="table">
              <thead>
                <tr>
                  <th scope="col">Tài liệu</th>
                  <th scope="col" style={{ width: 120 }}>Định dạng</th>
                  <th scope="col" style={{ width: 120 }}>Trạng thái</th>
                  <th scope="col" style={{ width: 150 }}>
                    <span className="sr-only">Thao tác</span>
                  </th>
                </tr>
              </thead>
              <tbody>
                {docs
                  .filter((d) => d.topicId === active)
                  .map((d) => (
                    <tr key={d.id}>
                      <td>{d.name}</td>
                      <td>{d.format}</td>
                      <td>
                        <PublishBadge status={d.status} />
                      </td>
                      <td>
                        <div className="table-actions">
                          {d.status === 'Đã công bố' ? (
                            <button type="button" className="link link--danger" onClick={() => setDocStatus(d.id, 'Nháp')}>
                              Gỡ công bố
                            </button>
                          ) : (
                            <button type="button" className="link" onClick={() => setDocStatus(d.id, 'Đã công bố')}>
                              Công bố
                            </button>
                          )}
                        </div>
                      </td>
                    </tr>
                  ))}
              </tbody>
            </table>
          </div>
        </div>

        <div className="a-col-side" style={{ flexBasis: 380 }}>
          <div className="card card--admin">
            <p className="t-h2">Tải lên tài liệu</p>
            <TextField id="doc-name" label="Tên tài liệu *" placeholder="Nhập tên hiển thị" value={upload.name} onChange={(e) => setUpload((u) => ({ ...u, name: e.target.value }))} />
            <SelectField
              label="Chủ đề *"
              options={topics.map((t) => ({ value: t.id, label: t.name }))}
              value={upload.topicId}
              onChange={(e) => setUpload((u) => ({ ...u, topicId: e.target.value }))}
            />
            <SelectField
              label="Loại *"
              options={['PDF', 'Hình ảnh', 'Liên kết video']}
              value={upload.format}
              onChange={(e) => setUpload((u) => ({ ...u, format: e.target.value as LearningDoc['format'] }))}
            />
            <Dropzone
              title={upload.file || 'Kéo thả hoặc chọn tệp'}
              hint={`PDF, JPG, PNG · tối đa ${site.maxFileSizeLabel}`}
              accept=".pdf,.jpg,.jpeg,.png"
              onFiles={(fs) => fs[0] && setUpload((u) => ({ ...u, file: fs[0].name, name: u.name || fs[0].name }))}
            />
            <TextField label="Hoặc liên kết video" placeholder="https://…" value={upload.link} onChange={(e) => setUpload((u) => ({ ...u, link: e.target.value }))} />
            <div className="row gap-10">
              <button type="button" className="btn btn--outline btn--block" onClick={() => saveDoc('Nháp')}>
                Lưu nháp
              </button>
              <button type="button" className="btn btn--block" onClick={() => saveDoc('Đã công bố')}>
                Công bố
              </button>
            </div>
          </div>
        </div>
      </div>
    </AdminLayout>
  );
}

interface DraftChoice {
  uid: string;
  text: string;
  explanation: string;
}
const newChoice = (): DraftChoice => ({ uid: crypto.randomUUID(), text: '', explanation: '' });
const letter = (i: number) => String.fromCharCode(65 + i);

/* QT06 – Soạn tình huống */
export function AdminNewSituation() {
  const nav = useNavigate();
  const { showToast } = useStore();
  const [title, setTitle] = useState('');
  const [question, setQuestion] = useState('');
  const [video, setVideo] = useState('');
  const [image, setImage] = useState('');
  const [choices, setChoices] = useState<DraftChoice[]>([newChoice(), newChoice(), newChoice()]);
  const [best, setBest] = useState(choices[1].uid);
  const [topicId, setTopicId] = useState(initialTopics[0].id);
  const [order, setOrder] = useState('3');
  const [status, setStatus] = useState<PublishStatus>('Nháp');
  const [retry, setRetry] = useState(true);
  const topicName = initialTopics.find((t) => t.id === topicId)?.name;

  const updateChoice = (uid: string, patch: Partial<DraftChoice>) => setChoices((cs) => cs.map((c) => (c.uid === uid ? { ...c, ...patch } : c)));

  return (
    <AdminLayout
      section="Học liệu"
      title="Thêm tình huống"
      subtitle={`${topicName} › Tình huống mới`}
      actions={
        <>
          <button type="button" className="btn btn--outline" onClick={() => showToast('Đã lưu nháp')}>
            Lưu nháp
          </button>
          <button
            type="button"
            className="btn"
            onClick={() => {
              showToast('Đã công bố tình huống');
              nav(R.qtLearning);
            }}
          >
            Công bố
          </button>
        </>
      }
    >
      <div className="a-cols">
        <div className="a-col-main">
          <div className="card card--admin">
            <p className="t-h2">Nội dung tình huống</p>
            <TextField label="Tiêu đề tình huống *" placeholder="Ví dụ: Bị chặn đường đòi tiền" value={title} onChange={(e) => setTitle(e.target.value)} />
            <TextAreaField label="Câu hỏi *" placeholder="Mô tả tình huống và câu hỏi cho sinh viên" value={question} onChange={(e) => setQuestion(e.target.value)} />
            <div className="row wrap gap-14" style={{ alignItems: 'flex-end' }}>
              <div style={{ flex: '1 1 240px' }}>
                <Dropzone compact iconSize={20} title={image || 'Tải hình minh họa (tùy chọn)'} accept="image/*" onFiles={(fs) => fs[0] && setImage(fs[0].name)} />
              </div>
              <div style={{ flex: '1 1 240px' }}>
                <TextField label="Liên kết video (tùy chọn)" placeholder="https://…" value={video} onChange={(e) => setVideo(e.target.value)} />
              </div>
            </div>
          </div>

          <div className="card card--admin">
            <div>
              <p className="t-h2">Lựa chọn &amp; giải thích</p>
              <p className="t-small t-muted">Mỗi lựa chọn có phần giải thích riêng hiển thị sau khi sinh viên chọn.</p>
            </div>
            {choices.map((c, i) => {
              const isBest = best === c.uid;
              const L = letter(i);
              return (
                <div key={c.uid} className="panel" style={{ padding: 16, background: isBest ? 'var(--c-primary-50)' : undefined }}>
                  <div className="row gap-10">
                    <p className="t-title grow">Lựa chọn {L}</p>
                    <label className="row gap-6 t-small" style={{ cursor: 'pointer', color: isBest ? 'var(--c-primary)' : 'var(--c-muted)' }}>
                      <input type="radio" name="best" checked={isBest} onChange={() => setBest(c.uid)} style={{ accentColor: 'var(--c-primary)', margin: 0 }} />
                      Cách ứng xử phù hợp nhất
                    </label>
                    <button
                      type="button"
                      aria-label={`Xóa lựa chọn ${L}`}
                      className="icon-btn"
                      disabled={choices.length <= 2}
                      onClick={() => {
                        setChoices((cs) => cs.filter((x) => x.uid !== c.uid));
                        if (isBest) setBest(choices.find((x) => x.uid !== c.uid)!.uid);
                      }}
                    >
                      <Icon name="x" size={18} color="var(--c-muted)" />
                    </button>
                  </div>
                  <TextField label="Nội dung lựa chọn *" placeholder={`Nhập lựa chọn ${L}`} value={c.text} onChange={(e) => updateChoice(c.uid, { text: e.target.value })} />
                  <TextAreaField
                    label="Giải thích *"
                    placeholder="Vì sao phù hợp / chưa phù hợp, nên làm gì"
                    value={c.explanation}
                    onChange={(e) => updateChoice(c.uid, { explanation: e.target.value })}
                  />
                </div>
              );
            })}
            <button
              type="button"
              className="row gap-8 t-small t-strong t-primary"
              style={{ justifyContent: 'center', padding: 14, border: '1.5px dashed var(--c-border-strong)', borderRadius: 12, background: 'none', cursor: 'pointer' }}
              onClick={() => setChoices((cs) => [...cs, newChoice()])}
              disabled={choices.length >= 6}
            >
              <Icon name="plus" size={18} color="var(--c-primary)" />
              Thêm lựa chọn
            </button>
          </div>
        </div>

        <div className="a-col-side" style={{ flexBasis: 340 }}>
          <div className="card card--admin">
            <p className="t-h2">Thiết lập</p>
            <SelectField label="Chủ đề *" options={initialTopics.map((t) => ({ value: t.id, label: t.name }))} value={topicId} onChange={(e) => setTopicId(e.target.value)} />
            <SelectField label="Thứ tự hiển thị" options={['1', '2', '3', '4', '5', '6']} value={order} onChange={(e) => setOrder(e.target.value)} />
            <SelectField label="Trạng thái" options={['Nháp', 'Đã công bố']} value={status} onChange={(e) => setStatus(e.target.value as PublishStatus)} />
            <SwitchRow title="Cho phép làm lại" hint="Sinh viên làm lại và xem lại giải thích" checked={retry} onChange={setRetry} />
          </div>
          <div className="panel panel--info" style={{ borderRadius: 14, padding: 20, gap: 8 }}>
            <div className="row gap-8">
              <Icon name="info" size={18} color="var(--c-primary)" />
              <p className="t-title t-primary">Xem trước</p>
            </div>
            <p className="t-small">Bản nháp chỉ cán bộ thấy. Bấm Công bố để sinh viên thấy tình huống này.</p>
            <Link to={R.svSituation(topicId, 1)} target="_blank" className="btn btn--outline btn--block">
              Xem trước trên điện thoại
            </Link>
          </div>
        </div>
      </div>
    </AdminLayout>
  );
}
