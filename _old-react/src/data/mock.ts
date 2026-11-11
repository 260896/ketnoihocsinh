/**
 * Dữ liệu mẫu theo đúng nội dung bản thiết kế.
 * Khi có backend, thay các export ở đây bằng lời gọi API trong `src/data/api.ts`.
 */
import type {
  AuditEntry,
  LearningDoc,
  Situation,
  StaffAccount,
  StudentAccount,
  Ticket,
  Topic,
} from './types';

/** `published` tình huống đã công bố + `drafts` bản nháp (sinh viên chỉ thấy bản đã công bố). */
const makeSituations = (topicId: string, published = 5, drafts = 0): Situation[] =>
  Array.from({ length: published + drafts }, (_, i) => i + 1).map((n) => ({
    id: `${topicId}-${n}`,
    title: `[Tên tình huống ${n}]`,
    question:
      '[Câu hỏi tình huống do cán bộ nhập. Ví dụ: Bạn thấy một bạn bị nhóm khác chặn đường đòi tiền sau giờ học. Bạn sẽ làm gì?]',
    media: n === 3 ? 'video' : n === 5 ? 'image' : undefined,
    kind: n === 3 ? 'Có video' : n === 5 ? 'Có hình' : 'Trắc nghiệm',
    status: n <= published ? 'Đã công bố' : 'Nháp',
    updatedAt: '[ngày]',
    bestKey: 'B',
    choices: ['A', 'B', 'C'].map((key) => ({
      key,
      text: `[Lựa chọn ${key}]`,
      explanation: `[Phần giải thích riêng cho lựa chọn ${key} do cán bộ nhập: vì sao phù hợp/chưa phù hợp, nên làm gì tiếp theo.]`,
    })),
  }));

export const topics: Topic[] = [
  {
    id: 'bao-luc-hoc-duong',
    name: 'Bạo lực học đường',
    icon: 'shield',
    description: '[Mô tả ngắn chủ đề do cán bộ nhập]',
    situations: makeSituations('bao-luc-hoc-duong', 5, 2),
  },
  {
    id: 'thuoc-la-dien-tu',
    name: 'Thuốc lá điện tử',
    icon: 'vapeOff',
    description: '[Mô tả ngắn chủ đề do cán bộ nhập]',
    situations: makeSituations('thuoc-la-dien-tu'),
  },
  {
    id: 'ung-xu-mang-xa-hoi',
    name: 'Ứng xử trên mạng xã hội',
    icon: 'messageLines',
    description: '[Mô tả ngắn chủ đề do cán bộ nhập]',
    situations: makeSituations('ung-xu-mang-xa-hoi'),
  },
];

/** Tiến độ ban đầu của sinh viên: topicId -> danh sách id tình huống đã làm. */
export const initialProgress: Record<string, string[]> = {
  'bao-luc-hoc-duong': ['bao-luc-hoc-duong-1', 'bao-luc-hoc-duong-2'],
  'thuoc-la-dien-tu': [],
  'ung-xu-mang-xa-hoi': [1, 2, 3, 4, 5].map((n) => `ung-xu-mang-xa-hoi-${n}`),
};

export const learningDocs: LearningDoc[] = [
  { id: 'd1', topicId: 'bao-luc-hoc-duong', name: '[Tên tài liệu].pdf', format: 'PDF', status: 'Đã công bố' },
  { id: 'd2', topicId: 'bao-luc-hoc-duong', name: '[Ảnh minh họa].jpg', format: 'Hình ảnh', status: 'Nháp' },
  { id: 'd3', topicId: 'bao-luc-hoc-duong', name: '[Video hướng dẫn]', format: 'Liên kết video', status: 'Đã công bố' },
];

export const issueGroups = ['[Nhóm vấn đề 1]', '[Nhóm vấn đề 2]', '[Nhóm vấn đề 3]', '[Nhóm vấn đề 4]'];
export const UNKNOWN_GROUP = 'Chưa rõ / Khác';

export const staffNames = ['[Cán bộ A]', '[Cán bộ B]', '[Cán bộ C]', '[Cán bộ D]', '[Cán bộ E]'];

const autoAck = (time = '[giờ]') => ({
  id: 'm0',
  from: 'system' as const,
  time,
  text: 'Hệ thống đã tiếp nhận phiếu. Đây chưa phải phản hồi của cán bộ.',
});

export const tickets: Ticket[] = [
  {
    id: '[MÃ-0014]',
    type: 'Đề nghị hỗ trợ',
    group: '[Nhóm 2]',
    urgent: true,
    anonymous: true,
    status: 'Đã tiếp nhận',
    priority: 'Khẩn',
    createdAt: '[ngày giờ]',
    deadline: '[Ngày giờ]',
    deadlineAlert: 'Còn 25 phút',
    assignee: '[Cán bộ A]',
    description: '[Nội dung sinh viên mô tả sự việc…]',
    occurredAt: '[Ngày giờ sự việc]',
    location: '[Địa điểm]',
    attachments: [],
    messages: [autoAck()],
    history: [{ time: '[giờ]', text: 'Phiếu được tạo (ẩn danh, khẩn)' }],
  },
  {
    id: '[MÃ-0013]',
    type: 'Phản ánh',
    group: UNKNOWN_GROUP,
    urgent: false,
    anonymous: true,
    status: 'Đã tiếp nhận',
    priority: 'Thường',
    createdAt: '[ngày giờ]',
    deadline: '[Ngày giờ]',
    assignee: null,
    description: '[Nội dung sinh viên mô tả sự việc…]',
    occurredAt: '[Ngày giờ sự việc]',
    location: '[Địa điểm]',
    attachments: [],
    messages: [autoAck()],
    history: [{ time: '[giờ]', text: 'Phiếu vào hàng chờ phân loại' }],
  },
  {
    id: '[MÃ-0012]',
    type: 'Đề nghị hỗ trợ',
    group: '[Nhóm vấn đề 2]',
    urgent: false,
    anonymous: true,
    status: 'Cần bổ sung',
    priority: 'Cao',
    createdAt: '[ngày giờ]',
    deadline: '[Ngày giờ]',
    assignee: '[Cán bộ B]',
    mine: true,
    description: '[Nội dung sinh viên mô tả sự việc…]',
    occurredAt: '[Ngày giờ sự việc]',
    location: '[Địa điểm]',
    attachments: [{ name: 'anh_chup_1.jpg', size: '1,2 MB' }],
    studentNote: 'Cán bộ cần thêm thông tin',
    hasUpdate: true,
    messages: [
      autoAck(),
      { id: 'm1', from: 'staff', author: '[Cán bộ B]', time: '[giờ]', text: '[Đề nghị sinh viên bổ sung thông tin…]' },
      { id: 'm2', from: 'student', time: '[giờ]', text: '[Nội dung bổ sung…]' },
    ],
    history: [
      { time: '[giờ]', text: '[Cán bộ B] đổi trạng thái → Cần bổ sung' },
      { time: '[giờ]', text: 'Hệ thống chuyển đến đầu mối [Nhóm 2]' },
      { time: '[giờ]', text: 'Phiếu được tạo (ẩn danh)' },
    ],
  },
  {
    id: '[MÃ-0011]',
    type: 'Phản ánh',
    group: '[Nhóm 1]',
    urgent: false,
    anonymous: false,
    status: 'Đang xem xét',
    priority: 'Cao',
    createdAt: '[ngày giờ]',
    deadline: '[Ngày giờ]',
    deadlineAlert: 'Quá hạn 3 giờ',
    assignee: '[Cán bộ C] → [Dự phòng]',
    description: '[Nội dung sinh viên mô tả sự việc…]',
    occurredAt: '[Ngày giờ sự việc]',
    location: '[Địa điểm]',
    attachments: [],
    messages: [autoAck()],
    history: [{ time: '[giờ]', text: 'Quá hạn → chuyển người dự phòng' }],
  },
  {
    id: '[MÃ-0010]',
    type: 'Góp ý',
    group: '[Nhóm 4]',
    urgent: false,
    anonymous: false,
    status: 'Đang xem xét',
    priority: 'Thường',
    createdAt: '[ngày giờ]',
    deadline: '[Ngày giờ]',
    assignee: '[Cán bộ A]',
    description: '[Nội dung sinh viên mô tả sự việc…]',
    occurredAt: '[Ngày giờ sự việc]',
    location: '[Địa điểm]',
    attachments: [],
    messages: [autoAck()],
    history: [],
  },
  {
    id: '[MÃ-0009]',
    type: 'Phản ánh',
    group: '[Nhóm 3]',
    urgent: false,
    anonymous: false,
    status: 'Đang xem xét',
    priority: 'Thường',
    createdAt: '[ngày giờ]',
    deadline: '—',
    assignee: '[Đầu mối độc lập]',
    description: '[Nội dung sinh viên mô tả sự việc…]',
    occurredAt: '[Ngày giờ sự việc]',
    location: '[Địa điểm]',
    attachments: [],
    studentNote: 'Cập nhật 2 ngày trước',
    messages: [autoAck()],
    history: [],
  },
  {
    id: '[MÃ-0008]',
    type: 'Góp ý',
    group: '[Nhóm 4]',
    urgent: false,
    anonymous: true,
    status: 'Đã có phản hồi',
    priority: 'Thường',
    createdAt: '[ngày giờ]',
    deadline: '—',
    assignee: '[Cán bộ B]',
    description: '[Nội dung sinh viên mô tả sự việc…]',
    occurredAt: '[Ngày giờ sự việc]',
    location: '[Địa điểm]',
    attachments: [],
    messages: [autoAck()],
    history: [],
  },
  {
    id: '[MÃ-0007]',
    type: 'Góp ý',
    group: '[Nhóm 4]',
    urgent: false,
    anonymous: true,
    status: 'Đã có phản hồi',
    priority: 'Thường',
    createdAt: '[ngày giờ]',
    deadline: '—',
    assignee: '[Cán bộ A]',
    description: '[Nội dung sinh viên mô tả sự việc…]',
    occurredAt: '[Ngày giờ sự việc]',
    location: '[Địa điểm]',
    attachments: [],
    studentNote: 'Cập nhật 5 ngày trước',
    hasUpdate: true,
    messages: [autoAck()],
    history: [],
  },
  {
    id: '[MÃ-0003]',
    type: 'Phản ánh',
    group: '[Nhóm 1]',
    urgent: false,
    anonymous: true,
    status: 'Đã tiếp nhận',
    priority: 'Thường',
    createdAt: '[ngày]',
    deadline: '[Ngày giờ]',
    assignee: '[Cán bộ C]',
    description: '[Nội dung sinh viên mô tả sự việc…]',
    occurredAt: '[Ngày giờ sự việc]',
    location: '[Địa điểm]',
    attachments: [],
    studentNote: 'Gửi [ngày]',
    messages: [autoAck()],
    history: [],
  },
];

/** Phiếu của sinh viên đang đăng nhập (màn SV19). */
export const myTicketIds = ['[MÃ-0012]', '[MÃ-0009]', '[MÃ-0007]', '[MÃ-0003]'];

export const replyTemplates = [
  { id: 't1', name: 'Đã tiếp nhận – đang xác minh', body: 'Cán bộ đã tiếp nhận phiếu và đang xác minh thông tin. [Nội dung mẫu]' },
  { id: 't2', name: 'Đề nghị bổ sung thông tin', body: 'Bạn vui lòng bổ sung thêm thông tin về [nội dung cần bổ sung]. [Nội dung mẫu]' },
  { id: 't3', name: 'Hướng dẫn liên hệ hỗ trợ trực tiếp', body: 'Bạn có thể liên hệ trực tiếp [đầu mối hỗ trợ] qua [kênh liên hệ]. [Nội dung mẫu]' },
];

export const revealBases = [
  '[Trường hợp 1 theo quy tắc]',
  '[Trường hợp 2 theo quy tắc]',
  '[Trường hợp 3 theo quy tắc]',
];

export const routing = [
  { group: '[Nhóm vấn đề 1]', primary: '[Cán bộ A]', backup: '[Cán bộ D]', independent: '[Cán bộ E]' },
  { group: '[Nhóm vấn đề 2]', primary: '[Cán bộ B]', backup: '[Cán bộ A]', independent: '[Cán bộ E]' },
  { group: '[Nhóm vấn đề 3]', primary: '[Cán bộ C]', backup: '[Cán bộ B]', independent: '[Cán bộ E]' },
  { group: '[Nhóm vấn đề 4]', primary: '[Cán bộ A]', backup: '[Cán bộ C]', independent: '[Cán bộ E]' },
  { group: UNKNOWN_GROUP, primary: 'Hàng chờ phân loại', backup: '[Cán bộ D]', independent: '[Cán bộ E]' },
];

export const staffAccounts: StaffAccount[] = [
  { id: 's1', name: '[Cán bộ A]', email: 'a@[tenmien]', role: 'Quản trị viên', canRevealIdentity: false, locked: false },
  { id: 's2', name: '[Cán bộ B]', email: 'b@[tenmien]', role: 'Xử lý phiếu', canRevealIdentity: false, locked: false },
  { id: 's3', name: '[Cán bộ E]', email: 'e@[tenmien]', role: 'Xử lý phiếu', canRevealIdentity: true, locked: false },
  { id: 's4', name: '[Cán bộ F]', email: 'f@[tenmien]', role: 'Xử lý phiếu', canRevealIdentity: false, locked: true },
];

export const studentAccounts: StudentAccount[] = [
  { id: 'u1', displayName: '[Biệt danh 1]', email: 'sv1@email.com', registeredAt: '[ngày]', locked: false },
  { id: 'u2', displayName: '[Biệt danh 2]', email: 'sv2@email.com', registeredAt: '[ngày]', locked: true },
];

export const auditLog: AuditEntry[] = [
  { id: 'l1', time: '[ngày giờ]', actor: '[Cán bộ E]', action: 'Mở danh tính', ticketId: '[MÃ-0011]', detail: 'Căn cứ: [trường hợp theo quy tắc] · Lý do: […]' },
  { id: 'l2', time: '[ngày giờ]', actor: '[Cán bộ B]', action: 'Tải tệp gốc', ticketId: '[MÃ-0012]', detail: 'anh_chup_1.jpg (bản gốc kèm siêu dữ liệu)' },
  { id: 'l3', time: '[ngày giờ]', actor: '[Cán bộ B]', action: 'Phản hồi', ticketId: '[MÃ-0012]', detail: 'Gửi tin nhắn trong hộp thư' },
  { id: 'l4', time: '[ngày giờ]', actor: '[Cán bộ A]', action: 'Phân loại', ticketId: '[MÃ-0013]', detail: 'Chưa rõ nhóm → [Nhóm vấn đề 3]' },
  { id: 'l5', time: '[ngày giờ]', actor: 'Hệ thống', action: 'Phân công', ticketId: '[MÃ-0011]', detail: 'Quá hạn → chuyển [Cán bộ D] (dự phòng)' },
  { id: 'l6', time: '[ngày giờ]', actor: '[Cán bộ A]', action: 'Ưu tiên', ticketId: '[MÃ-0014]', detail: 'Thường → Khẩn' },
  { id: 'l7', time: '[ngày giờ]', actor: '[Cán bộ A]', action: 'Tài khoản', ticketId: null, detail: 'Khóa tài khoản [Biệt danh 2]' },
];

/** Mã tham gia hợp lệ (dùng để mô phỏng lỗi "sai mã" ở SV02). */
export const VALID_JOIN_CODES = ['NK2026-XXXX'];
