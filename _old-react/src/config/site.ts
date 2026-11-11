/**
 * Cấu hình hiển thị chung. Các giá trị trong [NGOẶC VUÔNG] là chỗ trống
 * của bản thiết kế – thay bằng thông tin thật trước khi triển khai.
 * Về sau có thể nạp từ API cấu hình (màn QT07 – Cấu hình đầu mối).
 */
export const site = {
  projectName: '[TÊN DỰ ÁN]',
  activityName: '[TÊN HOẠT ĐỘNG NGOẠI KHÓA]',
  orgName: '[ĐƠN VỊ QUẢN LÝ]',
  websiteOrg: '[ĐƠN VỊ QUẢN LÝ WEBSITE]',
  unit: '[ĐƠN VỊ]',
  hotline: '[SỐ HỖ TRỢ KHẨN CẤP]',
  hotlineTel: '[SỐ KHẨN CẤP]',
  dutyHoursLabel: '[GIỜ TRỰC]',
  dutyStartLabel: '[GIỜ BẮT ĐẦU TRỰC]',
  fileTypesLabel: '[LOẠI TỆP]',
  maxFileSizeLabel: '[DUNG LƯỢNG]',
  verifyLinkTtl: '[THỜI HẠN]',
  staffEmailDomain: '[tenmien]',

  /** Giờ trực dùng để tính "ngoài giờ trực". Ngày: 0 = CN … 6 = T7. */
  duty: { start: '07:30', end: '17:00', days: [1, 2, 3, 4, 5] },
};

/** `?ngoaigio=1` / `?ngoaigio=0` trên URL để xem trước trạng thái ngoài giờ. */
export function isOutOfHours(now = new Date()): boolean {
  const override = new URLSearchParams(window.location.search).get('ngoaigio');
  if (override === '1') return true;
  if (override === '0') return false;

  const toMin = (hhmm: string) => {
    const [h, m] = hhmm.split(':').map(Number);
    return h * 60 + m;
  };
  const minutes = now.getHours() * 60 + now.getMinutes();
  const inDay = site.duty.days.includes(now.getDay());
  return !(inDay && minutes >= toMin(site.duty.start) && minutes < toMin(site.duty.end));
}

export const telHref = `tel:${site.hotlineTel}`;
