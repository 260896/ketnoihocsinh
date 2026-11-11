<?php
require __DIR__ . '/includes/bootstrap.php';

$topics = topics_all();
$outOfHours = is_out_of_hours();
$demo = (int) get('demo', '1');
$demoOptions = [
    'A. Bấm vào xem thử, thấy lạ thì thoát ra',
    'B. Không bấm link, chặn và báo cáo tin nhắn, báo cha mẹ hoặc thầy cô',
    'C. Nhập thông tin nhanh vì phần thưởng sắp hết hạn',
];
$sendSteps = [
    ['Chọn loại yêu cầu', 'Phản ánh, đề nghị hỗ trợ hoặc góp ý.', false],
    ['Chọn nhóm vấn đề', 'Chưa chắc nhóm nào? Phiếu vẫn được cán bộ phân loại.', false],
    ['Cần hỗ trợ ngay không?', 'Chọn “Có”, số hỗ trợ khẩn cấp hiện ra ngay.', true],
    ['Mô tả sự việc', 'Thời gian, địa điểm, đính kèm tệp (' . strtoupper(implode(', ', config()['file_types'])) . ', tối đa ' . config()['max_file_mb'] . ' MB).', false],
    ['Xem lại và gửi', 'Chọn gửi có danh tính hoặc ẩn danh, rồi xác nhận.', false],
];
$privacy = [
    ['eyeOff', 'Cán bộ xử lý', 'Đọc nội dung phiếu và trả lời bạn, không thấy danh tính với phiếu ẩn danh.'],
    ['lock', 'Người được chỉ định', 'Chỉ họ mở được danh tính. Mỗi lần mở đều được ghi nhật ký.'],
    ['file', 'Tệp đính kèm', 'Bản tải về đã xóa GPS, thiết bị, thời điểm chụp. Bản gốc chỉ cán bộ có thẩm quyền xem để xác minh, có ghi nhật ký.'],
    ['user', 'Đăng ký tối giản', 'Chỉ cần email và tên hiển thị, có thể là biệt danh. Không hỏi họ tên thật, mã học sinh hay lớp.'],
];

page_head('Học qua tình huống & Gửi phản ánh', ['assets/css/landing.css']);
?>
<div class="lp">
<?php if ($outOfHours): ?>
  <div class="lp-banner">
    <span style="font-weight:700">Ngoài giờ trực</span>
    <span>Phản ánh gửi bây giờ vẫn được ghi nhận và dự kiến xử lý từ <?= e(expected_processing_label()) ?>. Cần hỗ trợ ngay, gọi <a href="<?= e(tel_href()) ?>"><?= e(hotline()) ?></a></span>
  </div>
<?php endif; ?>

  <header class="lp-header">
    <div class="lp-container lp-header__inner">
      <a href="#top" class="lp-logo"><?= wordmark() ?></a>
      <nav aria-label="Điều hướng chính" class="lp-nav">
        <a href="<?= e(url('sv/hoc.php')) ?>">Học tình huống</a>
        <a href="<?= e(url('sv/gui-phieu.php')) ?>">Gửi phản ánh</a>
        <a href="#an-danh">Quyền riêng tư</a>
        <a href="<?= e(url('sv/phieu.php')) ?>">Theo dõi phiếu</a>
        <a href="<?= e(url('sv/dang-nhap.php')) ?>" class="lp-nav__login">Đăng nhập</a>
      </nav>
    </div>
  </header>

  <section id="top" class="lp-hero">
    <div class="lp-container lp-hero__inner">
      <div class="lp-hero__text">
        <span class="lp-pill"><?= e(SITE['slogan']) ?></span>
        <h1>Học cách ứng xử qua tình huống thật. <span>Lên tiếng an toàn</span> khi cần.</h1>
        <p class="lp-lead"><?= e(SITE['intro']) ?> Làm các tình huống trắc nghiệm về bạo lực học đường, thuốc lá điện tử, an toàn trên mạng xã hội, phòng chống tảo hôn và quấy rối tình dục. Gửi phản ánh, đề nghị hỗ trợ hoặc góp ý, có thể ẩn danh.</p>
        <div class="row wrap gap-12">
          <a href="<?= e(url('sv/hoc.php')) ?>" class="lp-cta">Bắt đầu học</a>
          <a href="<?= e(url('sv/gui-phieu.php')) ?>" class="lp-cta lp-cta--danger-outline">Gửi phản ánh</a>
        </div>
        <p style="font-size:14px;color:var(--c-muted)">Cần <strong>mã tham gia</strong> do nhà trường cung cấp để đăng ký.</p>
      </div>

      <div class="lp-demo-col" id="demo">
        <div class="lp-demo">
          <div class="row gap-8" style="justify-content:space-between;font-size:13px;color:var(--c-muted)">
            <span style="font-weight:600;color:var(--c-primary)">An toàn trên mạng xã hội</span>
            <span>Tình huống 1/1</span>
          </div>
          <div style="height:6px;background:var(--c-primary-100);border-radius:999px"><div style="width:100%;height:6px;background:var(--c-primary);border-radius:999px"></div></div>
          <p style="font-weight:700;font-size:17px;line-height:1.45">Em nhận được đường link “trúng thưởng” yêu cầu nhập số tài khoản ngân hàng và mã OTP. Em sẽ làm gì?</p>
          <div class="stack gap-10">
            <?php foreach ($demoOptions as $i => $o): ?>
              <a href="<?= e(url('', ['demo' => $i])) ?>#demo" class="lp-demo__opt<?= $demo === $i ? ' on' : '' ?>"<?= $demo === $i ? ' aria-current="true"' : '' ?>><?= e($o) ?></a>
            <?php endforeach; ?>
          </div>
          <div class="lp-demo__exp">
            <strong>Giải thích:</strong>
            <?= $demo === 1
                ? 'Không tương tác là cách an toàn nhất. Ngân hàng không bao giờ yêu cầu mật khẩu hay mã OTP.'
                : 'Chưa an toàn: chỉ cần bấm link hay nhập thông tin, em có thể bị chiếm tài khoản, mất tiền.' ?>
          </div>
          <span style="font-size:12px;color:var(--c-muted);text-align:center">Ví dụ minh họa giao diện</span>
        </div>
      </div>
    </div>
  </section>

  <section aria-label="Hỗ trợ khẩn cấp">
    <div class="lp-container lp-sos-wrap">
      <div class="lp-sos">
        <span class="lp-sos__icon"><?= icon('phone', 22, 'var(--c-danger)') ?></span>
        <div style="flex:1 1 320px;min-width:0">
          <p style="font-weight:700;color:var(--c-danger)">Bạn đang gặp nguy hiểm hoặc cần hỗ trợ ngay?</p>
          <p style="font-size:14px;color:var(--c-text-2)">Gọi ngay số hỗ trợ khẩn cấp, không cần chờ gửi phiếu. Giờ trực: <?= e(duty_label()) ?>.</p>
        </div>
        <a href="<?= e(tel_href()) ?>" class="lp-cta lp-cta--danger">Gọi <?= e(hotline()) ?></a>
      </div>
    </div>
  </section>

  <section id="hoc">
    <div class="lp-container lp-section" style="padding-top:80px;padding-bottom:40px">
      <div class="lp-head">
        <span class="lp-eyebrow">Học qua tình huống</span>
        <h2 class="lp-h2">Chọn một chủ đề, trả lời và xem giải thích cho từng lựa chọn</h2>
        <p class="lp-sub">Tiến độ được lưu theo tài khoản. Bạn có thể làm lại bất cứ lúc nào.</p>
      </div>
      <div class="lp-topics">
        <?php foreach ($topics as $t): ?>
          <a href="<?= e(url('sv/chu-de.php', ['id' => $t['id']])) ?>" class="lp-topic">
            <span class="lp-topic__icon"><?= icon($t['icon'], 24, 'var(--c-primary)') ?></span>
            <span class="lp-topic__name"><?= e($t['name']) ?></span>
            <span class="lp-topic__desc"><?= e($t['description']) ?></span>
            <span class="lp-topic__foot"><span><?= count(published_situations($t)) ?> tình huống</span><span>Bắt đầu →</span></span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section id="phieu">
    <div class="lp-container lp-section" style="padding-top:56px;padding-bottom:56px">
      <div class="lp-head">
        <span class="lp-eyebrow" style="color:var(--c-danger)">Gửi phiếu</span>
        <h2 class="lp-h2">Phản ánh, đề nghị hỗ trợ hoặc góp ý trong 5 bước</h2>
        <p class="lp-sub">Biểu mẫu hỏi từng bước. Bạn xem lại toàn bộ trước khi gửi.</p>
      </div>
      <ol class="lp-steps">
        <?php foreach ($sendSteps as $i => [$t, $d, $danger]): ?>
          <li class="lp-step<?= $danger ? ' lp-step--danger' : '' ?>">
            <span class="lp-step__num"><?= $i + 1 ?></span>
            <span style="font-weight:700"><?= e($t) ?></span>
            <span class="lp-step__desc"><?= e($d) ?></span>
          </li>
        <?php endforeach; ?>
      </ol>
      <div><a href="<?= e(url('sv/gui-phieu.php')) ?>" class="lp-cta lp-cta--danger">Gửi phiếu mới</a></div>
    </div>
  </section>

  <section id="an-danh" class="lp-dark">
    <div class="lp-container lp-dark__inner">
      <div class="stack gap-14" style="flex:1 1 340px;min-width:0">
        <span class="lp-eyebrow">Ẩn danh và quyền riêng tư</span>
        <h2 class="lp-h2">Nói rõ ai thấy gì, trước khi bạn gửi</h2>
        <p style="font-size:16px;color:var(--c-on-dark-muted)">Ẩn danh nghĩa là cán bộ xử lý không thấy tên và email của bạn. Hệ thống vẫn lưu liên kết với tài khoản ở nơi riêng, để người có thẩm quyền mở khi thật sự cần theo quy tắc <?= e(SITE['unit']) ?> ban hành.</p>
      </div>
      <div class="lp-dark__grid">
        <?php foreach ($privacy as [$ic, $t, $d]): ?>
          <div class="lp-dark__card"><?= icon($ic, 24, 'var(--c-on-dark-accent)') ?><span style="font-weight:700"><?= e($t) ?></span><span><?= e($d) ?></span></div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section id="theo-doi">
    <div class="lp-container lp-track">
      <div class="stack gap-14" style="flex:1 1 400px;min-width:0">
        <span class="lp-eyebrow">Theo dõi phiếu</span>
        <h2 class="lp-h2">Mỗi phiếu có mã riêng và hộp thư trao đổi</h2>
        <p class="lp-sub">Sau khi gửi, bạn nhận mã phiếu và một xác nhận tự động. Cán bộ trao đổi với bạn qua hộp thư gắn với mã phiếu, kể cả phiếu ẩn danh.</p>
        <p style="color:var(--c-text-2);font-size:15px">Email chỉ báo “phiếu có cập nhật”, không kèm nội dung. Đăng nhập để đọc chi tiết.</p>
      </div>
      <div class="lp-track__demo">
        <div class="row wrap gap-8" style="justify-content:space-between">
          <span style="font-weight:700;font-size:17px">Phản ánh #KN-2026-0001</span>
          <span style="font-size:13px;color:var(--c-muted)">Đề nghị hỗ trợ · Ẩn danh</span>
        </div>
        <ol aria-label="Trạng thái phiếu" class="lp-status">
          <li style="background:var(--c-primary);color:#fff">Đã tiếp nhận</li>
          <li style="background:var(--c-danger-50);color:var(--c-danger)">Cần bổ sung</li>
          <li>Đang xem xét</li>
          <li>Đã có phản hồi</li>
        </ol>
        <div class="lp-chat">
          <div class="lp-msg"><b style="color:var(--c-muted)">Xác nhận tự động</b>Hệ thống đã nhận phiếu của bạn. Đây chưa phải phản hồi của cán bộ.</div>
          <div class="lp-msg"><b style="color:var(--c-primary)">Cán bộ phụ trách</b>Em vui lòng bổ sung thêm thời gian và địa điểm cụ thể nhé.</div>
          <div class="lp-msg lp-msg--me">Dạ, sự việc xảy ra chiều thứ Ba ở sân sau ạ.</div>
        </div>
        <form class="lp-reply" action="<?= e(url('sv/phieu.php')) ?>" method="get">
          <label for="lp-reply" class="sr-only">Nhập tin nhắn</label>
          <input id="lp-reply" type="text" placeholder="Nhập tin nhắn…" class="input">
          <button type="submit" class="btn btn--sm">Gửi</button>
        </form>
      </div>
    </div>
  </section>

  <section id="dang-nhap" class="lp-start">
    <div class="lp-container lp-start__inner">
      <h2 class="lp-h2" style="max-width:640px">Bắt đầu trong 3 bước</h2>
      <ol class="lp-start__steps">
        <?php foreach ([['Nhập mã tham gia', 'Mã do nhà trường cung cấp.'], ['Email và tên hiển thị', 'Tên hiển thị có thể là biệt danh.'], ['Xác thực email', 'Bấm liên kết gửi về hộp thư để kích hoạt tài khoản.']] as $i => [$t, $d]): ?>
          <li class="lp-start__step"><span class="lp-start__num"><?= $i + 1 ?></span><span><strong class="d-block"><?= e($t) ?></strong><span style="font-size:14px;color:var(--c-text-2)"><?= e($d) ?></span></span></li>
        <?php endforeach; ?>
      </ol>
      <div class="row wrap gap-12 justify-center">
        <a href="<?= e(url('sv/dang-ky.php')) ?>" class="lp-cta">Đăng ký tài khoản</a>
        <a href="<?= e(url('sv/dang-nhap.php')) ?>" class="lp-cta lp-cta--outline">Tôi đã có tài khoản</a>
      </div>
    </div>
  </section>

  <footer class="lp-footer">
    <div class="lp-container lp-footer__inner">
      <div class="lp-footer__col" style="flex:1 1 280px">
        <?= wordmark('sm') ?>
        <span><?= e(SITE['slogan']) ?></span>
        <span>Đơn vị quản lý: <?= e(SITE['org_name']) ?> · <?= e(SITE['domain']) ?></span>
        <span>Thời gian tiếp nhận: <?= e(duty_label()) ?></span>
      </div>
      <div class="lp-footer__col" style="flex:1 1 220px">
        <span style="font-weight:700;color:var(--c-text)">Hỗ trợ khẩn cấp</span>
        <a href="<?= e(tel_href()) ?>" style="color:var(--c-danger);font-weight:700;text-decoration:none;font-size:18px"><?= e(hotline()) ?></a>
      </div>
      <nav aria-label="Liên kết chân trang" class="lp-footer__col" style="flex:1 1 200px">
        <a href="#top">Chính sách bảo mật</a>
        <a href="#top">Quy tắc sử dụng</a>
        <a href="#an-danh">Ai xem được phiếu của tôi?</a>
      </nav>
      <div class="lp-footer__col" style="flex:1 1 160px">
        <span style="font-weight:700;color:var(--c-text)">Cán bộ</span>
        <a href="<?= e(url('qt/dang-nhap.php')) ?>">Đăng nhập trang quản trị</a>
      </div>
    </div>
    <?= demo_notice() ?>
  </footer>
</div>
<?php page_foot();
