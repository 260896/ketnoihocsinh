/** Đường dẫn tập trung – tránh viết cứng URL rải rác trong các màn hình. */
const enc = encodeURIComponent;

export const R = {
  landing: '/',

  // Sinh viên
  svRegister: '/sv/dang-ky',
  svVerify: '/sv/xac-thuc',
  svLogin: '/sv/dang-nhap',
  svForgot: '/sv/quen-mat-khau',
  svNewPassword: '/sv/mat-khau-moi',
  svHome: '/sv/hoc',
  svTopic: (topicId: string) => `/sv/hoc/${topicId}`,
  svSituation: (topicId: string, index: number) => `/sv/hoc/${topicId}/${index}`,
  svTopicDone: (topicId: string) => `/sv/hoc/${topicId}/hoan-thanh`,
  svAccount: '/sv/tai-khoan',
  svSend: '/sv/gui-phieu',
  svSendStep: (step: number) => `/sv/gui-phieu/buoc-${step}`,
  svSendDone: (ticketId: string) => `/sv/gui-phieu/thanh-cong/${enc(ticketId)}`,
  svOutOfHours: '/sv/ngoai-gio-truc',
  svTickets: '/sv/phieu',
  svTicket: (id: string) => `/sv/phieu/${enc(id)}`,

  // Cán bộ quản trị
  qtLogin: '/qt/dang-nhap',
  qtTickets: '/qt/phieu',
  qtTicket: (id: string) => `/qt/phieu/${enc(id)}`,
  qtReveal: (id: string) => `/qt/phieu/${enc(id)}/mo-danh-tinh`,
  qtLearning: '/qt/hoc-lieu',
  qtNewSituation: '/qt/hoc-lieu/tinh-huong-moi',
  qtConfig: '/qt/cau-hinh',
  qtAccounts: '/qt/tai-khoan',
  qtAudit: '/qt/nhat-ky',
};
