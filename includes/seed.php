<?php
declare(strict_types=1);

/**
 * Dữ liệu khởi tạo cho bản CHẠY THỬ "KẾT NỐI HỌC SINH" – toàn bộ là dữ liệu GIẢ LẬP.
 * Nguồn: "Bộ hồ sơ chạy thử Kết nối học sinh" (8 mục), chuyển bằng tools/nhap-ho-so.php → includes/du-lieu-mau.json.
 * Chỉ dùng khi tệp data/<tên>.json chưa tồn tại. Muốn làm lại từ đầu: sao lưu rồi xóa các tệp .json trong data/.
 */
function seed_data(string $name): array
{
    return match ($name) {
        'config'  => seed_config(),
        'topics'  => seed_topics(),
        'users'   => seed_users(),
        'tickets' => seed_tickets(),
        'audit'   => seed_audit(),
        'docs'    => seed_docs(),
        'codes'   => seed_codes(),
        'ticket_owners' => seed_ticket_owners(),
        default   => [],
    };
}

/** Dữ liệu từ bộ hồ sơ (đã chuyển đổi). */
function seed_source(): array
{
    static $src = null;
    if ($src === null) {
        $src = json_decode((string) @file_get_contents(__DIR__ . '/du-lieu-mau.json'), true) ?: [];
    }
    return $src;
}

/* Nhóm vấn đề – mục 2 bộ hồ sơ (mã nguồn trong ngoặc) */
const G_BULLY    = 'Bạo lực học đường, bắt nạt';                    // BLHD
const G_VAPE     = 'Sử dụng thuốc lá điện tử';                      // TLDT
const G_SOCIAL   = 'Các vấn đề trên mạng xã hội';                   // MXH
const G_HARASS   = 'Quấy rối, xâm hại';                             // QRTD
const G_CHILD    = 'Nguy cơ tảo hôn hoặc bị ép kết hôn';            // TAOHON – nội dung bổ sung đề xuất
const G_FEEDBACK = 'Góp ý về hoạt động và môi trường học đường';    // GOPY
const G_STAFF    = 'Phản ánh liên quan trực tiếp đến người đang xử lý';
// Có trong Brief nhưng nhà trường CHƯA giao đầu mối → phiếu vào hàng chờ phân loại (CLAUDE.md 11.2)
const G_TEACHING = 'Chất lượng giảng dạy, đào tạo';
const G_FACILITY = 'Cơ sở vật chất';

const GROUP_CODES = [
    'BLHD' => G_BULLY, 'TLDT' => G_VAPE, 'MXH' => G_SOCIAL, 'QRTD' => G_HARASS,
    'TAOHON' => G_CHILD, 'GOPY' => G_FEEDBACK,
];

/* Mã cán bộ – mục 1 bộ hồ sơ */
const U_ADMIN = 'u_admin';   // NV01 Nguyễn Minh Anh – quản trị, được mở danh tính
const U_BAO   = 'u_bao';     // NV02 Trần Quốc Bảo – công tác học sinh
const U_HA    = 'u_ha';      // NV03 Lê Thu Hà – tư vấn, hỗ trợ học sinh
const U_NAM   = 'u_nam';     // NV04 Phạm Hoàng Nam – dự phòng
const U_TUNG  = 'u_tung';    // NV05 Võ Thanh Tùng – tiếp nhận độc lập, được mở danh tính
const U_GV    = 'u_gv';      // Giáo viên quản lý học liệu (bổ sung – hồ sơ chưa nêu tên)
const U_HS    = 'u_hs1';     // Học sinh chạy thử E2400249
const STAFF_CODES = ['NV01' => U_ADMIN, 'NV02' => U_BAO, 'NV03' => U_HA, 'NV04' => U_NAM, 'NV05' => U_TUNG];

function seed_config(): array
{
    return [
        'duty_start'         => '08:00',
        'duty_end'           => '17:00',
        'duty_days'          => [1, 2, 3, 4, 5],
        'urgent_ack_minutes' => 15,
        'first_reply_days'   => 2,      // ngày làm việc
        'fallback'           => true,
        'mail_enabled'       => false,  // hồ sơ mục 1: không gửi email ra ngoài khi chạy thử
        'hotline'            => '1900 0000', // SỐ GIẢ LẬP – hồ sơ mục 3: không tạo nút gọi
        'file_types'         => ['pdf', 'jpg', 'jpeg', 'png', 'mp4'],
        'max_file_mb'        => 10,     // 10 MB = 10.000.000 byte (hồ sơ mục 5)
        'max_files'          => 5,
        'pass_threshold'     => (int) (seed_source()['pass_threshold'] ?? 2), // đạt khi đúng ≥ 2/3 câu
        'groups'             => [G_BULLY, G_VAPE, G_SOCIAL, G_HARASS, G_CHILD, G_FEEDBACK, G_TEACHING, G_FACILITY, G_STAFF],
        // primary = người phụ trách; backup = người nhận khi người chính vắng mặt / quá hạn;
        // independent = người tiếp nhận độc lập khi có xung đột lợi ích.
        // Ô trống = nguồn chưa quy định (hồ sơ mục 2) → hệ thống báo quản trị viên, không âm thầm bỏ qua.
        'routing'            => [
            G_BULLY       => ['primary' => U_BAO,  'backup' => U_NAM,  'independent' => U_TUNG],
            G_VAPE        => ['primary' => U_BAO,  'backup' => '',     'independent' => U_TUNG],
            G_SOCIAL      => ['primary' => U_HA,   'backup' => '',     'independent' => U_TUNG],
            G_HARASS      => ['primary' => U_HA,   'backup' => U_TUNG, 'independent' => U_TUNG],
            G_CHILD       => ['primary' => U_HA,   'backup' => U_TUNG, 'independent' => U_TUNG],
            G_FEEDBACK    => ['primary' => U_BAO,  'backup' => '',     'independent' => U_TUNG],
            G_TEACHING    => ['primary' => '',     'backup' => '',     'independent' => U_TUNG],
            G_FACILITY    => ['primary' => '',     'backup' => '',     'independent' => U_TUNG],
            G_STAFF       => ['primary' => U_TUNG, 'backup' => '',     'independent' => U_TUNG],
            ISSUE_UNKNOWN => ['primary' => '',     'backup' => '',     'independent' => U_TUNG],
        ],
        'templates' => [
            ['id' => 't1', 'name' => 'Đã ghi nhận – chuyển người phù hợp', 'body' => 'Nhà trường đã ghi nhận phản ánh của em. Nội dung sẽ được chuyển đến người phù hợp. Em có thể bổ sung thông tin qua kênh theo dõi bảo mật. Việc tiếp nhận chưa phải là kết luận về người hoặc sự việc được phản ánh.'],
            ['id' => 't2', 'name' => 'Đề nghị bổ sung thông tin', 'body' => 'Để hỗ trợ em tốt hơn, em có thể bổ sung thời gian, địa điểm hoặc thông tin em biết khi em sẵn sàng. Em không cần kể lại những chi tiết khiến em khó chịu.'],
            ['id' => 't3', 'name' => 'Hướng dẫn gặp trực tiếp', 'body' => 'Em có thể gặp trực tiếp cán bộ tư vấn tại phòng tư vấn học đường trong giờ làm việc (08:00–17:00, thứ Hai đến thứ Sáu). Nếu có nguy cơ tức thời, em hãy rời nơi nguy hiểm nếu có thể và tìm người lớn đáng tin cậy hoặc dịch vụ khẩn cấp chính thức; không chờ website phản hồi.'],
            ...seed_group_templates(),
        ],
    ];
}

/** BẢN NHÁP mẫu trả lời theo từng nhóm – PM / nhà trường duyệt lại câu chữ (task #29). */
function seed_group_templates(): array
{
    $list = [
        [G_BULLY, 'Bắt nạt – đã tiếp nhận, giữ an toàn', 'Cảm ơn em đã lên tiếng. Thầy/cô đã tiếp nhận và sẽ xác minh sự việc. Trong lúc chờ, em hãy tránh ở một mình với người có hành vi bắt nạt và báo ngay nếu sự việc lặp lại. Em không cần tự quay clip hay tìm thêm chứng cứ.'],
        [G_VAPE, 'Thuốc lá điện tử – đã tiếp nhận', 'Cảm ơn em đã phản ánh. Nhà trường sẽ kiểm tra khu vực em nêu. Em không cần tự can thiệp, giữ hộ hay thu giữ thiết bị. Nếu ai đó có biểu hiện bất thường sau khi dùng, hãy tìm người lớn và hỗ trợ y tế ngay.'],
        [G_SOCIAL, 'Mạng xã hội – không cung cấp mật khẩu, OTP', 'Thầy/cô đã nhận phản ánh. Em không nhập mật khẩu, OTP hay chuyển tiền, không gửi tiếp đường link. Nếu đã lỡ nhập mật khẩu, em đổi ngay qua ứng dụng hoặc trang chính thức và báo người lớn.'],
        [G_HARASS, 'Quấy rối – ưu tiên an toàn', 'Thầy/cô đã nhận phản ánh của em. An toàn của em là ưu tiên. Em không có lỗi trong chuyện này và không cần tự tiếp xúc với người bị phản ánh để lấy thêm tài liệu. Em có thể gặp riêng cán bộ tư vấn.'],
        [G_CHILD, 'Tảo hôn – tìm người hỗ trợ an toàn', 'Thầy/cô đã nhận phản ánh của em. Việc kết hôn phải đủ tuổi và tự nguyện. Nếu gia đình đang gây áp lực, em có thể gặp riêng cán bộ tư vấn hoặc người lớn đáng tin cậy ngoài gia đình. Em không cần tự đối đầu nếu có thể bị gây hại.'],
        [G_FEEDBACK, 'Góp ý – cảm ơn và kế hoạch', 'Cảm ơn góp ý của em. Nhà trường sẽ xem xét và đưa vào kế hoạch phù hợp. Thầy/cô sẽ cập nhật cho em khi có kết quả.'],
        [G_TEACHING, 'Giảng dạy – đã chuyển bộ phận chuyên môn', 'Cảm ơn em đã góp ý về việc giảng dạy. Ý kiến được chuyển tới bộ phận chuyên môn để xem xét, không nêu danh tính của em.'],
        [G_FACILITY, 'Cơ sở vật chất – đã ghi nhận', 'Cảm ơn em đã báo. Nhà trường đã ghi nhận vị trí và tình trạng em nêu để kiểm tra, sửa chữa.'],
        [G_STAFF, 'Liên quan cán bộ – tiếp nhận độc lập', 'Phản ánh của em được người tiếp nhận độc lập xử lý, không chuyển cho người bị phản ánh. Việc tiếp nhận chưa phải là kết luận về người hoặc sự việc được phản ánh.'],
    ];
    $out = [];
    foreach ($list as $i => [$g, $name, $body]) {
        $out[] = ['id' => 'tg' . ($i + 1), 'name' => $name, 'body' => $body, 'group' => $g];
    }
    return $out;
}

/**
 * Học liệu gốc của nhà trường: 5 chủ đề × 3 câu hỏi ("Bộ câu hỏi trắc nghiệm – Nội dung học tập ban đầu"),
 * nội dung học tập từ tài liệu thảo luận chủ đề, kèm infographic, ảnh minh họa, video. Nhập bằng tools/nhap-hoc-lieu.php.
 */
function seed_topics(): array
{
    seed_ensure_media();
    $topics = seed_source()['topics'] ?? [];
    $now = date('c');
    foreach ($topics as &$t) {
        foreach ($t['situations'] as &$s) $s['updated_at'] = $now;
    }
    return $topics;
}

function seed_users(): array
{
    $pw = password_hash(DEMO_PASSWORD, PASSWORD_DEFAULT);
    $src = array_column(seed_source()['staff'] ?? [], null, 'id');
    // Hồ sơ mục 1: đăng nhập thử bằng email example.com; email nguồn (có dấu cách) giữ ở source_email để đối chiếu
    $staff = function (string $code, string $role, bool $reveal) use ($pw, $src) {
        $s = $src[$code];
        return [
            'id' => STAFF_CODES[$code], 'staff_code' => $code, 'role' => 'staff', 'name' => $s['name'],
            'email' => $s['test_email'], 'source_email' => $s['source_email'], 'password' => $pw,
            'staff_role' => $role, 'can_reveal' => $reveal, 'absent' => false, 'locked' => false, 'verified' => true,
            'created_at' => date('c', strtotime('-30 days')),
        ];
    };
    $users = [
        // Chỉ NV01 và NV05 được mở thông tin định danh (hồ sơ mục 1, 4)
        $staff('NV01', ROLE_ADMIN, true),
        $staff('NV02', ROLE_HANDLER, false),
        $staff('NV03', ROLE_HANDLER, false),
        $staff('NV04', ROLE_HANDLER, false),
        $staff('NV05', ROLE_HANDLER, true),
        [
            'id' => U_GV, 'staff_code' => 'GV01', 'role' => 'staff', 'name' => 'Giáo viên chạy thử', 'email' => 'giaovien@example.com',
            'password' => $pw, 'staff_role' => ROLE_TEACHER, 'can_reveal' => false, 'absent' => false, 'locked' => false, 'verified' => true,
            'created_at' => date('c', strtotime('-30 days')),
        ],
        [
            'id' => U_HS, 'role' => 'student', 'name' => 'E2400249', 'email' => 'E2400249.test@example.com',
            'password' => $pw, 'locked' => false, 'verified' => true, 'progress' => [], 'answers' => [],
            'created_at' => date('c', strtotime('-7 days')),
        ],
    ];
    // Bản ghi danh tính giả lập (DD-xxxx) của phản ánh "bảo mật danh tính" – lưu như tài khoản học sinh riêng
    foreach (seed_source()['identities'] ?? [] as $d) {
        $users[] = [
            'id' => 'u_' . strtolower(str_replace('-', '', $d['id'])), 'identity_ref' => $d['id'], 'role' => 'student',
            'name' => $d['name'], 'email' => $d['contact'], 'password' => $pw, 'locked' => false, 'verified' => true,
            'progress' => [], 'answers' => [], 'created_at' => date('c', strtotime('-7 days')),
        ];
    }
    return $users;
}

/** Tài liệu kèm chủ đề: PDF, video, infographic (đã công bố). */
function seed_docs(): array
{
    seed_ensure_media();
    return seed_source()['docs'] ?? [];
}

/**
 * Chép media học liệu từ nguon/hoc-lieu/<id>/ (lưu trong Git) vào data/uploads/ (không lưu trong Git) nếu chưa có,
 * để bản cài mới vẫn có đủ ảnh, infographic, video, PDF. Không ghi đè tệp đã có.
 */
function seed_ensure_media(): void
{
    static $done = false;
    if ($done) return;
    $done = true;
    foreach (glob(ROOT . '/nguon/hoc-lieu/*', GLOB_ONLYDIR) ?: [] as $dir) {
        $id = basename($dir);
        if ($id === 'thao-luan') continue;
        foreach (glob($dir . '/*') ?: [] as $file) {
            $name = basename($file);
            $to = match (true) {
                $name === 'video.mp4'    => "docs/hoc-lieu/$id.mp4",
                $name === 'tai-lieu.pdf' => "docs/hoc-lieu/$id.pdf",
                default                  => "images/hoc-lieu/$id-$name",
            };
            $dest = UPLOAD_DIR . '/' . $to;
            if (is_file($dest)) continue;
            if (!is_dir(dirname($dest))) mkdir(dirname($dest), 0775, true);
            copy($file, $dest);
        }
    }
}

/** Cặp mã tham gia + email. HS2026 là mã nguồn của tài khoản E2400249 (đã dùng). */
function seed_codes(): array
{
    $now = date('c');
    $codes = [['code' => 'HS2026', 'email' => 'e2400249.test@example.com', 'used' => true, 'created_at' => $now, 'used_at' => $now]];
    foreach (range(1, 5) as $i) {
        $codes[] = ['code' => 'DEMO-000' . $i, 'email' => 'demo' . $i . '@example.com', 'used' => false, 'created_at' => $now, 'used_at' => null];
    }
    return $codes;
}

/**
 * 12 phản ánh mẫu – mục 8 bộ hồ sơ. Thời điểm nhận giữ đúng như hồ sơ để đối chiếu các ca thời hạn ở mục 3.
 * Theo Brief 2.5: mọi phiếu ẩn danh vẫn lưu liên kết danh tính TÁCH RIÊNG (ticket_owners.json), chỉ người được chỉ định mở.
 * Phiếu "anonymous" trong hồ sơ không có bản ghi danh tính → không có liên kết; phiếu "confidential_identity" → liên kết tới DD-xxxx.
 */
function seed_tickets(): array
{
    $out = [];
    foreach (seed_source()['reports'] ?? [] as $r) {
        $group = GROUP_CODES[$r['category']] ?? ISSUE_UNKNOWN;
        $urgent = $r['priority'] === 'urgent';
        $received = strtotime($r['received_at']);
        $assignee = $r['assigned_to'] ? (STAFF_CODES[$r['assigned_to']] ?? null) : null;
        $conflict = array_values(array_map(fn ($c) => STAFF_CODES[$c] ?? $c, $r['conflicted_staff'] ?? []));
        $absent = array_values(array_map(fn ($c) => STAFF_CODES[$c] ?? $c, $r['unavailable_staff'] ?? []));
        $at = date('c', $received);

        $history = [['time' => $at, 'text' => 'Phiếu được tạo (ẩn danh' . ($urgent ? ', cần phản hồi ngay' : '') . ')']];
        if ($conflict) array_unshift($history, ['time' => $at, 'text' => 'Kiểm tra xung đột lợi ích: loại ' . count($conflict) . ' cán bộ có liên quan khỏi hồ sơ']);
        if ($absent) array_unshift($history, ['time' => $at, 'text' => 'Người phụ trách chính vắng mặt → chuyển người dự phòng']);
        array_unshift($history, ['time' => $at, 'text' => $assignee ? 'Hệ thống chuyển đến ' . seed_staff_name($assignee) : 'Chờ chỉ định người tiếp nhận độc lập khác']);

        $out[] = [
            'code' => $r['id'], 'type' => $r['category'] === 'GOPY' ? 'Góp ý' : (str_contains($r['body'], 'hỗ trợ') ? 'Đề nghị hỗ trợ' : 'Phản ánh'),
            'title' => $r['title'], 'group' => $group, 'anonymous' => true, 'urgent' => $urgent, 'status' => 'Đã tiếp nhận', 'priority' => $urgent ? PRIORITY_NOW : PRIORITY_LATER,
            'created_at' => $at, 'deadline' => date('c', ticket_deadline($received, $urgent)), 'assignee' => $assignee,
            'conflict' => $conflict, 'needs_independent' => $r['status'] === 'pending_independent_assignment',
            'acknowledged_at' => null, 'escalated_at' => null, 'reminded_at' => null,
            'description' => $r['body'], 'occurred_at' => '', 'location' => '', 'attachments' => [],
            'messages' => [['from' => 'system', 'author_id' => null, 'time' => $at, 'text' => 'Tin nhắn tự động: phản ánh ' . $r['id'] . ' đã được ghi nhận. Đây chưa phải phản hồi của cán bộ.']],
            'history' => $history, 'has_update' => false, 'revealed_to' => [],
        ];
    }
    return $out;
}

function seed_staff_name(string $id): string
{
    foreach (seed_source()['staff'] ?? [] as $s) if ((STAFF_CODES[$s['id']] ?? '') === $id) return $s['name'];
    return $id;
}

/** Liên kết phiếu ↔ bản ghi danh tính, lưu tách khỏi nội dung phiếu (Brief 2.5). */
function seed_ticket_owners(): array
{
    $out = [];
    foreach (seed_source()['reports'] ?? [] as $r) {
        if ($r['identity_ref']) $out[$r['id']] = 'u_' . strtolower(str_replace('-', '', $r['identity_ref']));
    }
    return $out;
}

/** Nhật ký mẫu NK-0001…0003 (hồ sơ mục 4). Không ghi giá trị danh tính vào nhật ký. */
function seed_audit(): array
{
    $a = fn ($time, $actor, $actorId, $action, $ticket, $detail) => [
        'id' => new_id('l'), 'time' => $time, 'actor' => $actor, 'actor_id' => $actorId,
        'action' => $action, 'ticket' => $ticket, 'detail' => $detail,
    ];
    return [
        $a('2026-11-10T10:05:00+07:00', 'Võ Thanh Tùng', U_TUNG, 'Mở danh tính', 'KN-2026-0004', 'NK-0003 · Căn cứ: Cần liên hệ trực tiếp để bảo đảm an toàn cho học sinh · Lý do: liên hệ riêng để bố trí hỗ trợ theo đề nghị của người phản ánh (MD-0001) · Kết quả: cho phép'),
        $a('2026-11-10T09:40:00+07:00', 'Trần Quốc Bảo', U_BAO, 'Từ chối truy cập', 'KN-2026-0004', 'NK-0002 · Yêu cầu xem danh tính · Kết quả: từ chối (không phải người được chỉ định)'),
        $a('2026-11-10T09:10:00+07:00', 'Trần Quốc Bảo', U_BAO, 'Xem phiếu', 'KN-2026-0001', 'NK-0001 · Đọc nội dung · Kết quả: cho phép'),
    ];
}
