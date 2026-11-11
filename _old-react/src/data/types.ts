import type { IconName } from '@/components/ui/Icon';

// ---------- Học liệu ----------
export interface Choice {
  key: string; // A, B, C…
  text: string;
  explanation: string;
}

export interface Situation {
  id: string;
  title: string;
  question: string;
  media?: 'image' | 'video';
  choices: Choice[];
  bestKey: string;
  kind: 'Trắc nghiệm' | 'Có video' | 'Có hình';
  status: PublishStatus;
  updatedAt: string;
}

export interface Topic {
  id: string;
  name: string;
  icon: IconName;
  description: string;
  situations: Situation[];
}

export type PublishStatus = 'Đã công bố' | 'Nháp';

export interface LearningDoc {
  id: string;
  topicId: string;
  name: string;
  format: 'PDF' | 'Hình ảnh' | 'Liên kết video';
  status: PublishStatus;
}

// ---------- Phiếu ----------
export type RequestType = 'Phản ánh' | 'Đề nghị hỗ trợ' | 'Góp ý';
export type TicketStatus = 'Đã tiếp nhận' | 'Cần bổ sung' | 'Đang xem xét' | 'Đã có phản hồi';
export type Priority = 'Khẩn' | 'Cao' | 'Thường';

export interface Message {
  id: string;
  from: 'system' | 'staff' | 'student';
  author?: string;
  time: string;
  text: string;
}

export interface Attachment {
  name: string;
  size: string;
}

export interface Ticket {
  id: string; // ví dụ "[MÃ-0012]"
  type: RequestType;
  group: string;
  urgent: boolean;
  anonymous: boolean;
  status: TicketStatus;
  priority: Priority;
  createdAt: string;
  deadline: string;
  deadlineAlert?: string; // "Còn 25 phút", "Quá hạn 3 giờ"
  assignee: string | null; // null = hàng chờ phân loại
  mine?: boolean;
  description: string;
  occurredAt: string;
  location: string;
  attachments: Attachment[];
  messages: Message[];
  history: { time: string; text: string }[];
  studentNote?: string; // dòng phụ trên danh sách của sinh viên
  hasUpdate?: boolean;
}

// ---------- Tài khoản ----------
export interface StaffAccount {
  id: string;
  name: string;
  email: string;
  role: 'Quản trị viên' | 'Xử lý phiếu';
  canRevealIdentity: boolean;
  locked: boolean;
}

export interface StudentAccount {
  id: string;
  displayName: string;
  email: string;
  registeredAt: string;
  locked: boolean;
}

// ---------- Nhật ký ----------
export type AuditAction =
  | 'Mở danh tính'
  | 'Tải tệp gốc'
  | 'Phản hồi'
  | 'Phân loại'
  | 'Phân công'
  | 'Ưu tiên'
  | 'Tài khoản';

export interface AuditEntry {
  id: string;
  time: string;
  actor: string;
  action: AuditAction;
  ticketId: string | null;
  detail: string;
}
