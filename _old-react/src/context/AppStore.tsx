import { createContext, useCallback, useContext, useMemo, useState, type ReactNode } from 'react';
import * as mock from '@/data/mock';
import type { AuditEntry, Message, RequestType, Ticket } from '@/data/types';

/**
 * Kho trạng thái dùng chung (thay cho backend trong giai đoạn giao diện).
 * Khi nối API, giữ nguyên chữ ký các hàm dưới đây và đổi phần thân thành lời gọi API.
 */

export interface TicketDraft {
  type: RequestType | null;
  group: string | null;
  urgent: boolean | null;
  description: string;
  occurredAt: string;
  location: string;
  files: { name: string; size: string }[];
  anonymous: boolean;
}

export const emptyDraft: TicketDraft = {
  type: null,
  group: null,
  urgent: null,
  description: '',
  occurredAt: '',
  location: '',
  files: [],
  anonymous: true,
};

interface AppStore {
  // Học
  progress: Record<string, string[]>;
  markDone: (topicId: string, situationId: string) => void;
  resetTopic: (topicId: string) => void;

  // Phiếu
  tickets: Ticket[];
  myTicketIds: string[];
  getTicket: (id: string) => Ticket | undefined;
  updateTicket: (id: string, patch: Partial<Ticket>) => void;
  addMessage: (id: string, msg: Omit<Message, 'id' | 'time'>) => void;

  draft: TicketDraft;
  setDraft: (patch: Partial<TicketDraft>) => void;
  submitDraft: () => Ticket;

  // Nhật ký
  audit: AuditEntry[];
  log: (entry: Omit<AuditEntry, 'id' | 'time'>) => void;

  // Thông báo nhanh
  toast: string | null;
  showToast: (text: string) => void;
}

const Ctx = createContext<AppStore | null>(null);

const nowLabel = () =>
  new Date().toLocaleString('vi-VN', { hour: '2-digit', minute: '2-digit', day: '2-digit', month: '2-digit' });

let seq = 15;

export function AppStoreProvider({ children }: { children: ReactNode }) {
  const [progress, setProgress] = useState(mock.initialProgress);
  const [tickets, setTickets] = useState<Ticket[]>(mock.tickets);
  const [myTicketIds, setMyTicketIds] = useState<string[]>(mock.myTicketIds);
  const [draft, setDraftState] = useState<TicketDraft>(emptyDraft);
  const [audit, setAudit] = useState<AuditEntry[]>(mock.auditLog);
  const [toast, setToast] = useState<string | null>(null);

  const markDone = useCallback((topicId: string, situationId: string) => {
    setProgress((p) => {
      const done = p[topicId] ?? [];
      return done.includes(situationId) ? p : { ...p, [topicId]: [...done, situationId] };
    });
  }, []);

  const resetTopic = useCallback((topicId: string) => {
    setProgress((p) => ({ ...p, [topicId]: [] }));
  }, []);

  const getTicket = useCallback((id: string) => tickets.find((t) => t.id === id), [tickets]);

  const updateTicket = useCallback((id: string, patch: Partial<Ticket>) => {
    setTickets((ts) => ts.map((t) => (t.id === id ? { ...t, ...patch } : t)));
  }, []);

  const addMessage = useCallback((id: string, msg: Omit<Message, 'id' | 'time'>) => {
    setTickets((ts) =>
      ts.map((t) =>
        t.id === id
          ? { ...t, messages: [...t.messages, { ...msg, id: crypto.randomUUID(), time: nowLabel() }] }
          : t,
      ),
    );
  }, []);

  const setDraft = useCallback((patch: Partial<TicketDraft>) => {
    setDraftState((d) => ({ ...d, ...patch }));
  }, []);

  const log = useCallback((entry: Omit<AuditEntry, 'id' | 'time'>) => {
    setAudit((a) => [{ ...entry, id: crypto.randomUUID(), time: nowLabel() }, ...a]);
  }, []);

  const submitDraft = useCallback((): Ticket => {
    const id = `[MÃ-${String(seq++).padStart(4, '0')}]`;
    const urgent = draft.urgent === true;
    const ticket: Ticket = {
      id,
      type: draft.type ?? 'Phản ánh',
      group: draft.group ?? mock.UNKNOWN_GROUP,
      urgent,
      anonymous: draft.anonymous,
      status: 'Đã tiếp nhận',
      priority: urgent ? 'Khẩn' : 'Thường',
      createdAt: nowLabel(),
      deadline: '[Ngày giờ]',
      assignee: draft.group && draft.group !== mock.UNKNOWN_GROUP ? '[Đầu mối nhóm]' : null,
      description: draft.description || '[Không có mô tả]',
      occurredAt: draft.occurredAt || '—',
      location: draft.location || '—',
      attachments: draft.files,
      studentNote: `Gửi ${nowLabel()}`,
      messages: [
        {
          id: 'm0',
          from: 'system',
          time: nowLabel(),
          text: urgent
            ? 'Hệ thống đã tiếp nhận phiếu khẩn của bạn và báo cho cán bộ trực. Đây chưa phải phản hồi của cán bộ.'
            : 'Hệ thống đã tiếp nhận phiếu. Đây chưa phải phản hồi của cán bộ.',
        },
      ],
      history: [{ time: nowLabel(), text: `Phiếu được tạo${draft.anonymous ? ' (ẩn danh)' : ''}` }],
    };
    setTickets((ts) => [ticket, ...ts]);
    setMyTicketIds((ids) => [id, ...ids]);
    setDraftState(emptyDraft);
    return ticket;
  }, [draft]);

  const showToast = useCallback((text: string) => {
    setToast(text);
    window.setTimeout(() => setToast(null), 2200);
  }, []);

  const value = useMemo<AppStore>(
    () => ({
      progress,
      markDone,
      resetTopic,
      tickets,
      myTicketIds,
      getTicket,
      updateTicket,
      addMessage,
      draft,
      setDraft,
      submitDraft,
      audit,
      log,
      toast,
      showToast,
    }),
    [progress, markDone, resetTopic, tickets, myTicketIds, getTicket, updateTicket, addMessage, draft, setDraft, submitDraft, audit, log, toast, showToast],
  );

  return (
    <Ctx.Provider value={value}>
      {children}
      {toast && (
        <div className="toast" role="status">
          {toast}
        </div>
      )}
    </Ctx.Provider>
  );
}

export function useStore() {
  const ctx = useContext(Ctx);
  if (!ctx) throw new Error('useStore phải dùng bên trong <AppStoreProvider>');
  return ctx;
}
