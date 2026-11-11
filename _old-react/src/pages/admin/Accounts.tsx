import { useState } from 'react';
import { SelectField, SwitchRow, TextField } from '@/components/ui/Form';
import { site } from '@/config/site';
import { useStore } from '@/context/AppStore';
import { issueGroups, staffAccounts, studentAccounts } from '@/data/mock';
import type { StaffAccount, StudentAccount } from '@/data/types';
import { AdminLayout } from '@/layouts/AdminLayout';

const StateBadge = ({ locked }: { locked: boolean }) =>
  locked ? <span className="badge badge--danger-solid">Đã khóa</span> : <span className="badge badge--info">Hoạt động</span>;

const emptyForm = { name: '', email: '', role: 'Xử lý phiếu' as StaffAccount['role'], group: issueGroups[1], reveal: false };

/* QT08 – Tài khoản */
export default function AdminAccounts() {
  const { showToast, log } = useStore();
  const [tab, setTab] = useState<'staff' | 'student'>('staff');
  const [staff, setStaff] = useState<StaffAccount[]>(staffAccounts);
  const [students, setStudents] = useState<StudentAccount[]>(studentAccounts);
  const [form, setForm] = useState(emptyForm);

  const toggleStudent = (s: StudentAccount) => {
    setStudents((ss) => ss.map((x) => (x.id === s.id ? { ...x, locked: !x.locked } : x)));
    log({ actor: '[Tên cán bộ]', action: 'Tài khoản', ticketId: null, detail: `${s.locked ? 'Mở khóa' : 'Khóa'} tài khoản ${s.displayName}` });
  };

  const create = () => {
    if (!form.name.trim() || !/^\S+@\S+$/.test(form.email)) return showToast('Nhập họ tên và email hợp lệ');
    setStaff((s) => [...s, { id: crypto.randomUUID(), name: form.name.trim(), email: form.email.trim(), role: form.role, canRevealIdentity: form.reveal, locked: false }]);
    log({ actor: '[Tên cán bộ]', action: 'Tài khoản', ticketId: null, detail: `Tạo tài khoản cán bộ ${form.name.trim()}` });
    setForm(emptyForm);
    setTab('staff');
    showToast('Đã tạo tài khoản. Cán bộ sẽ nhận email đặt mật khẩu.');
  };

  return (
    <AdminLayout
      section="Tài khoản"
      title="Tài khoản"
      subtitle="Tài khoản cán bộ do quản trị viên tạo. Khóa được tài khoản sinh viên vi phạm."
      actions={
        <button type="button" className="btn" onClick={() => document.getElementById('new-staff-name')?.focus()}>
          Tạo tài khoản cán bộ
        </button>
      }
    >
      <div className="a-cols">
        <div className="a-col-main">
          <div className="row wrap gap-8">
            <button type="button" className={`chip${tab === 'staff' ? ' chip--active' : ''}`} aria-pressed={tab === 'staff'} onClick={() => setTab('staff')}>
              Cán bộ · {staff.length}
            </button>
            <button type="button" className={`chip${tab === 'student' ? ' chip--active' : ''}`} aria-pressed={tab === 'student'} onClick={() => setTab('student')}>
              Sinh viên · [120]
            </button>
          </div>

          {tab === 'staff' && (
            <div className="table-wrap">
              <table className="table">
                <thead>
                  <tr>
                    <th scope="col">Cán bộ</th>
                    <th scope="col" style={{ width: 140 }}>Vai trò</th>
                    <th scope="col" style={{ width: 120 }}>Mở danh tính</th>
                    <th scope="col" style={{ width: 120 }}>Trạng thái</th>
                    <th scope="col" style={{ width: 80 }}>
                      <span className="sr-only">Thao tác</span>
                    </th>
                  </tr>
                </thead>
                <tbody>
                  {staff.map((s) => (
                    <tr key={s.id}>
                      <td>
                        {s.name} · {s.email}
                      </td>
                      <td>{s.role}</td>
                      <td>{s.canRevealIdentity ? <span className="badge badge--danger">Có</span> : <span className="badge badge--muted">Không</span>}</td>
                      <td>
                        <StateBadge locked={s.locked} />
                      </td>
                      <td>
                        <button type="button" className="link" onClick={() => showToast('Màn sửa tài khoản sẽ bổ sung sau')}>
                          Sửa
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}

          {tab === 'student' && (
            <>
              <div>
                <p className="t-h2">Sinh viên</p>
                <p className="t-small t-muted">Chỉ hiển thị tên hiển thị và email. Không hiển thị tài khoản nào đã gửi phiếu ẩn danh nào.</p>
              </div>
              <div className="table-wrap">
                <table className="table">
                  <thead>
                    <tr>
                      <th scope="col">Tên hiển thị · Email</th>
                      <th scope="col" style={{ width: 130 }}>Ngày đăng ký</th>
                      <th scope="col" style={{ width: 120 }}>Trạng thái</th>
                      <th scope="col" style={{ width: 110 }}>
                        <span className="sr-only">Thao tác</span>
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    {students.map((s) => (
                      <tr key={s.id}>
                        <td>
                          {s.displayName} · {s.email}
                        </td>
                        <td>{s.registeredAt}</td>
                        <td>
                          <StateBadge locked={s.locked} />
                        </td>
                        <td>
                          <button type="button" className={`link${s.locked ? '' : ' link--danger'}`} onClick={() => toggleStudent(s)}>
                            {s.locked ? 'Mở khóa' : 'Khóa'}
                          </button>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </>
          )}
        </div>

        <div className="a-col-side" style={{ flexBasis: 380 }}>
          <form
            className="card card--admin"
            onSubmit={(e) => {
              e.preventDefault();
              create();
            }}
          >
            <p className="t-h2">Tạo tài khoản cán bộ</p>
            <TextField id="new-staff-name" label="Họ tên *" placeholder="Nhập họ tên cán bộ" value={form.name} onChange={(e) => setForm((f) => ({ ...f, name: e.target.value }))} />
            <TextField label="Email *" type="email" placeholder={`canbo@${site.staffEmailDomain}`} value={form.email} onChange={(e) => setForm((f) => ({ ...f, email: e.target.value }))} />
            <SelectField
              label="Vai trò *"
              options={['Xử lý phiếu', 'Quản trị viên']}
              value={form.role}
              onChange={(e) => setForm((f) => ({ ...f, role: e.target.value as StaffAccount['role'] }))}
            />
            <SelectField label="Nhóm vấn đề phụ trách" options={issueGroups} value={form.group} onChange={(e) => setForm((f) => ({ ...f, group: e.target.value }))} />
            <SwitchRow
              title="Quyền mở danh tính phiếu ẩn danh"
              hint="Chỉ cấp cho người được chỉ định theo quy tắc"
              checked={form.reveal}
              onChange={(v) => setForm((f) => ({ ...f, reveal: v }))}
            />
            <p className="t-small t-muted">Cán bộ nhận email đặt mật khẩu lần đầu.</p>
            <div className="row gap-10">
              <button type="button" className="btn btn--outline btn--block" onClick={() => setForm(emptyForm)}>
                Hủy
              </button>
              <button type="submit" className="btn btn--block">
                Tạo tài khoản
              </button>
            </div>
          </form>
        </div>
      </div>
    </AdminLayout>
  );
}
