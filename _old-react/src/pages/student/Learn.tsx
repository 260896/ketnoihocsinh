import { useState } from 'react';
import { Link, Navigate, useNavigate, useParams } from 'react-router-dom';
import { ChoiceCard, EmergencyCard } from '@/components/ui/Bits';
import { Icon } from '@/components/ui/Icon';
import { useStore } from '@/context/AppStore';
import { topics } from '@/data/mock';
import type { Topic } from '@/data/types';
import { MobileScreen } from '@/layouts/MobileScreen';
import { R } from '@/lib/routes';

const published = (t: Topic) => t.situations.filter((s) => s.status === 'Đã công bố');

function useTopic() {
  const { topicId } = useParams();
  return topics.find((t) => t.id === topicId);
}

/* SV07 – Trang chủ (Học) */
export function StudentHome() {
  const { progress } = useStore();
  return (
    <MobileScreen title="Xin chào, [Tên hiển thị]" tab="Học">
      <EmergencyCard />
      <div className="row gap-12">
        <h2 className="t-h2 grow">Chủ đề học</h2>
        <p className="t-small t-strong t-primary">{topics.length} chủ đề</p>
      </div>
      {topics.map((t) => {
        const total = published(t).length;
        const done = (progress[t.id] ?? []).length;
        const label = done === 0 ? 'Chưa bắt đầu' : done >= total ? `${total}/${total} · Hoàn thành` : `${done}/${total} tình huống`;
        return (
          <Link key={t.id} to={R.svTopic(t.id)} className="list-card" style={{ gap: 12 }}>
            <div className="row gap-12">
              <span className="icon-circle" style={{ width: 44, height: 44, borderRadius: 12, background: 'var(--c-primary-50)' }}>
                <Icon name={t.icon} size={22} color="var(--c-primary)" />
              </span>
              <span className="stack gap-2 grow">
                <span className="t-title">{t.name}</span>
                <span className="t-small t-muted">{label}</span>
              </span>
              <Icon name="chevronRight" size={20} color="var(--c-muted)" />
            </div>
            <div className="progress" aria-hidden="true">
              <div style={{ width: `${total ? Math.min(100, (done / total) * 100) : 0}%` }} />
            </div>
          </Link>
        );
      })}
      <Link
        to={R.svSend}
        className="row gap-12"
        style={{ textDecoration: 'none', padding: 16, borderRadius: 16, background: 'var(--c-danger-50)', color: 'var(--c-text)' }}
      >
        <span className="icon-circle" style={{ width: 44, height: 44, borderRadius: 12, background: 'var(--c-danger)' }}>
          <Icon name="send" size={22} color="#fff" />
        </span>
        <span className="grow">
          <span className="t-title t-danger" style={{ display: 'block' }}>
            Gửi phản ánh, đề nghị hỗ trợ
          </span>
          <span className="t-small">Có thể gửi ẩn danh</span>
        </span>
        <Icon name="chevronRight" size={20} color="var(--c-danger)" />
      </Link>
    </MobileScreen>
  );
}

/* SV08 – Chủ đề */
export function TopicPage() {
  const topic = useTopic();
  const { progress } = useStore();
  if (!topic) return <Navigate to={R.svHome} replace />;

  const list = published(topic);
  const done = progress[topic.id] ?? [];
  const nextIdx = list.findIndex((s) => !done.includes(s.id));

  return (
    <MobileScreen title={topic.name} back={R.svHome} tab="Học">
      <div className="stack gap-10" style={{ background: 'var(--c-primary)', borderRadius: 16, padding: 18 }}>
        <p style={{ fontSize: 22, fontWeight: 700, color: '#fff' }}>{topic.name}</p>
        <p className="t-small" style={{ color: '#DCE6F5' }}>
          {topic.description}
        </p>
        <div style={{ height: 6, background: 'var(--c-primary-dark)', borderRadius: 3, overflow: 'hidden' }}>
          <div style={{ width: `${(done.length / list.length) * 100}%`, height: 6, background: '#fff', borderRadius: 3 }} />
        </div>
        <p className="t-small t-strong" style={{ color: '#fff' }}>
          Đã làm {done.length}/{list.length} tình huống
        </p>
      </div>
      <h2 className="t-h2">Tình huống</h2>
      {list.map((s, i) => {
        const isDone = done.includes(s.id);
        const isNext = i === nextIdx;
        return (
          <Link
            key={s.id}
            to={R.svSituation(topic.id, i + 1)}
            className={`list-card row gap-12${isNext ? ' list-card--current' : ''}`}
            style={{ flexDirection: 'row', padding: isNext ? 13 : 14 }}
          >
            <span
              className="icon-circle"
              style={{ width: 32, height: 32, background: isDone ? 'var(--c-primary)' : isNext ? 'var(--c-primary-50)' : 'var(--c-neutral)' }}
            >
              {isDone ? (
                <Icon name="check" size={16} color="#fff" />
              ) : (
                <span className="t-small t-strong" style={{ color: isNext ? 'var(--c-primary)' : 'var(--c-muted)' }}>
                  {i + 1}
                </span>
              )}
            </span>
            <span className="grow">
              <span className="t-title" style={{ display: 'block' }}>
                {s.title}
              </span>
              <span className={`t-small ${isNext ? 't-primary' : 't-muted'}`}>
                {isDone ? 'Đã làm · Làm lại được' : isNext ? 'Làm tiếp' : 'Chưa làm'}
              </span>
            </span>
            <Icon name="chevronRight" size={18} color="var(--c-muted)" />
          </Link>
        );
      })}
    </MobileScreen>
  );
}

/* SV09 – Tình huống & giải thích */
export function SituationPage() {
  const { topicId, index } = useParams();
  // key: reset trạng thái lựa chọn khi chuyển sang tình huống khác
  return <Situation key={`${topicId}/${index}`} index={index} />;
}

function Situation({ index }: { index?: string }) {
  const topic = useTopic();
  const nav = useNavigate();
  const { markDone } = useStore();
  const [picked, setPicked] = useState<string | null>(null);
  const [showAll, setShowAll] = useState(false);

  if (!topic) return <Navigate to={R.svHome} replace />;
  const list = published(topic);
  const i = Number(index) - 1;
  const s = list[i];
  if (!s) return <Navigate to={R.svTopic(topic.id)} replace />;

  const pick = (key: string) => {
    setPicked(key);
    markDone(topic.id, s.id);
  };
  const reset = () => {
    setPicked(null);
    setShowAll(false);
  };
  const next = () => {
    nav(i + 1 < list.length ? R.svSituation(topic.id, i + 2) : R.svTopicDone(topic.id));
  };
  const pickedChoice = s.choices.find((c) => c.key === picked);
  const others = s.choices.filter((c) => c.key !== picked);

  return (
    <MobileScreen
      title={`Tình huống ${i + 1}/${list.length}`}
      back={R.svTopic(topic.id)}
      footer={
        <>
          <button type="button" className="btn btn--outline" onClick={reset}>
            Làm lại
          </button>
          <button type="button" className="btn" onClick={next} disabled={!picked}>
            Tình huống tiếp
          </button>
        </>
      }
    >
      <div className="progress" aria-hidden="true">
        <div style={{ width: `${((i + 1) / list.length) * 100}%` }} />
      </div>
      {s.media && (
        <div
          role="img"
          aria-label="Hình hoặc video minh họa"
          className="stack gap-8 items-center"
          style={{ height: 180, borderRadius: 16, background: 'var(--c-neutral)', justifyContent: 'center' }}
        >
          <Icon name="play" size={40} color="var(--c-muted)" />
          <p className="t-small t-muted">[Hình hoặc video minh họa]</p>
        </div>
      )}
      <p className="t-h2" style={{ lineHeight: 1.5 }}>
        {s.question}
      </p>
      {s.choices.map((c) => (
        <ChoiceCard
          key={c.key}
          selected={picked === c.key}
          title={`${c.key}. ${c.text}`}
          hint={picked === c.key ? 'Bạn đã chọn' : undefined}
          onSelect={() => pick(c.key)}
        />
      ))}
      {pickedChoice && (
        <div className="stack gap-8" style={{ background: 'var(--c-primary-50)', borderRadius: 16, padding: 16 }} aria-live="polite">
          <div className="row gap-8">
            <Icon name="info" size={18} color="var(--c-primary)" />
            <p className="t-title t-primary">Giải thích cho lựa chọn {pickedChoice.key}</p>
          </div>
          <p>{pickedChoice.explanation}</p>
          {showAll ? (
            others.map((c) => (
              <div key={c.key} className="stack gap-4" style={{ borderTop: '1px solid var(--c-primary-100)', paddingTop: 8 }}>
                <p className="t-small t-strong">Lựa chọn {c.key}</p>
                <p className="t-small">{c.explanation}</p>
              </div>
            ))
          ) : (
            <button type="button" className="link link--lg" style={{ alignSelf: 'flex-start' }} onClick={() => setShowAll(true)}>
              Xem giải thích lựa chọn {others.map((c) => c.key).join(', ')}
            </button>
          )}
        </div>
      )}
    </MobileScreen>
  );
}

/* SV10 – Hoàn thành chủ đề */
export function TopicDone() {
  const topic = useTopic();
  const { progress, resetTopic } = useStore();
  const nav = useNavigate();
  if (!topic) return <Navigate to={R.svHome} replace />;
  const total = published(topic).length;
  const done = Math.min(total, (progress[topic.id] ?? []).length);

  return (
    <MobileScreen title={topic.name} back={R.svTopic(topic.id)} tab="Học">
      <div className="stack gap-14 items-center" style={{ paddingTop: 32 }}>
        <span className="icon-circle" style={{ width: 88, height: 88, background: 'var(--c-primary-50)' }}>
          <Icon name="award" size={44} color="var(--c-primary)" />
        </span>
        <h2 className="t-h1 t-center">Bạn đã hoàn thành chủ đề</h2>
        <p className="t-muted t-center">
          {done}/{total} tình huống · Tiến độ đã được lưu vào tài khoản
        </p>
        <div className="card self-stretch">
          <div className="row gap-12">
            <p className="t-muted grow">Tình huống đã làm</p>
            <p className="t-title">
              {done}/{total}
            </p>
          </div>
          <div className="row gap-12">
            <p className="t-muted grow">Có thể làm lại</p>
            <p className="t-title">Bất cứ lúc nào</p>
          </div>
        </div>
        <Link to={R.svHome} className="btn btn--block">
          Học chủ đề khác
        </Link>
        <button
          type="button"
          className="btn btn--outline btn--block"
          onClick={() => {
            resetTopic(topic.id);
            nav(R.svSituation(topic.id, 1));
          }}
        >
          Làm lại chủ đề này
        </button>
      </div>
    </MobileScreen>
  );
}
