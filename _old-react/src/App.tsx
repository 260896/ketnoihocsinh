import { Navigate, Route, Routes, useParams } from 'react-router-dom';
import Landing from '@/pages/Landing';
import ScreenIndex from '@/pages/ScreenIndex';
import { ForgotPassword, Login, NewPassword, Register, VerifyEmail } from '@/pages/student/Auth';
import { StudentHome, TopicPage, SituationPage, TopicDone } from '@/pages/student/Learn';
import { StudentAccount } from '@/pages/student/Account';
import { OutOfHours, SendDone, SendEntry, SendStep1, SendStep2, SendStep3, SendStep4, SendStep5 } from '@/pages/student/Send';
import { MyTickets, TicketDetail } from '@/pages/student/Tickets';
import AdminLogin from '@/pages/admin/Login';
import { AdminTicketDetail, AdminTickets } from '@/pages/admin/Tickets';
import { AdminLearning, AdminNewSituation } from '@/pages/admin/Learning';
import AdminConfig from '@/pages/admin/Config';
import AdminAccounts from '@/pages/admin/Accounts';
import AdminAudit from '@/pages/admin/Audit';

/** Tạo lại component khi :id đổi để state của form không bị giữ từ phiếu trước. */
function AdminTicketRoute() {
  const { id } = useParams();
  return <AdminTicketDetail key={id} />;
}

export default function App() {
  return (
    <Routes>
      <Route path="/" element={<Landing />} />
      <Route path="/man-hinh" element={<ScreenIndex />} />

      {/* Sinh viên (mobile) */}
      <Route path="/sv">
        <Route index element={<Navigate to="hoc" replace />} />
        <Route path="dang-ky" element={<Register />} />
        <Route path="xac-thuc" element={<VerifyEmail />} />
        <Route path="dang-nhap" element={<Login />} />
        <Route path="quen-mat-khau" element={<ForgotPassword />} />
        <Route path="mat-khau-moi" element={<NewPassword />} />

        <Route path="hoc" element={<StudentHome />} />
        <Route path="hoc/:topicId" element={<TopicPage />} />
        <Route path="hoc/:topicId/hoan-thanh" element={<TopicDone />} />
        <Route path="hoc/:topicId/:index" element={<SituationPage />} />

        <Route path="tai-khoan" element={<StudentAccount />} />

        <Route path="gui-phieu" element={<SendEntry />} />
        <Route path="gui-phieu/buoc-1" element={<SendStep1 />} />
        <Route path="gui-phieu/buoc-2" element={<SendStep2 />} />
        <Route path="gui-phieu/buoc-3" element={<SendStep3 />} />
        <Route path="gui-phieu/buoc-4" element={<SendStep4 />} />
        <Route path="gui-phieu/buoc-5" element={<SendStep5 />} />
        <Route path="gui-phieu/thanh-cong/:id" element={<SendDone />} />
        <Route path="ngoai-gio-truc" element={<OutOfHours />} />

        <Route path="phieu" element={<MyTickets />} />
        <Route path="phieu/:id" element={<TicketDetail />} />
      </Route>

      {/* Cán bộ quản trị */}
      <Route path="/qt">
        <Route index element={<Navigate to="phieu" replace />} />
        <Route path="dang-nhap" element={<AdminLogin />} />
        <Route path="phieu" element={<AdminTickets />} />
        <Route path="phieu/:id" element={<AdminTicketRoute />} />
        <Route path="phieu/:id/mo-danh-tinh" element={<AdminTicketRoute />} />
        <Route path="hoc-lieu" element={<AdminLearning />} />
        <Route path="hoc-lieu/tinh-huong-moi" element={<AdminNewSituation />} />
        <Route path="cau-hinh" element={<AdminConfig />} />
        <Route path="tai-khoan" element={<AdminAccounts />} />
        <Route path="nhat-ky" element={<AdminAudit />} />
      </Route>

      <Route path="*" element={<Navigate to="/" replace />} />
    </Routes>
  );
}
