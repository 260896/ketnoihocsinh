<?php
/**
 * Chuyển "Bộ hồ sơ chạy thử Kết nối học sinh" (JSON) thành dữ liệu mẫu của website: includes/du-lieu-mau.json
 *   php tools/nhap-ho-so.php                 (mặc định đọc nguon/ho-so/)
 *   php tools/nhap-ho-so.php "<thư mục khác>"  (chứa 01_…json, 07_…json, 08_…json)
 * Sau đó xóa data/*.json (sao lưu trước) để website tạo lại dữ liệu từ includes/seed.php.
 * Chỉ chạy từ dòng lệnh.
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Forbidden'); }

$dir = rtrim($argv[1] ?? (dirname(__DIR__) . '/nguon/ho-so'), '/\\');
$read = function (string $name) use ($dir): array {
    $f = "$dir/$name";
    if (!is_file($f)) { fwrite(STDERR, "Không thấy $f\n"); exit(1); }
    $j = json_decode((string) file_get_contents($f), true);
    if (!is_array($j)) { fwrite(STDERR, "$f không phải JSON hợp lệ\n"); exit(1); }
    return $j;
};
$learn = $read('07_Hoc_lieu_5_chu_de.json');
$acc   = $read('08_Tai_khoan_ho_so_mau.json');
$admin = $read('01_Quan_tri_tiep_nhan.json');

// Mã chủ đề trong hồ sơ → id, icon, mô tả ngắn trên website
$topicMeta = [
    'CD01' => ['bao-luc-hoc-duong', 'shield', 'Nhận biết hành vi bắt nạt, cách ứng xử an toàn và tìm người hỗ trợ.'],
    'CD02' => ['thuoc-la-dien-tu', 'vapeOff', 'Nhận biết nguy cơ, quy định và cách từ chối khi bị rủ rê.'],
    'CD03' => ['an-toan-mang-xa-hoi', 'messageLines', 'Nhận diện lừa đảo, bảo vệ mật khẩu, OTP và xác minh qua kênh chính thức.'],
    'CD04' => ['phong-chong-tao-hon', 'heart', 'Điều kiện tuổi kết hôn, quyền tự nguyện và cách tìm người hỗ trợ an toàn.'],
    'CD05' => ['phong-chong-quay-roi', 'hand', 'Tôn trọng ranh giới, không đổ lỗi, ưu tiên an toàn và hỗ trợ.'],
];

$topics = [];
foreach ($learn['test_data']['topics'] as $order => $t) {
    [$id, $icon, $desc] = $topicMeta[$t['id']] ?? [strtolower($t['id']), 'book', ''];
    $situations = [];
    foreach ($t['questions'] as $qi => $q) {
        $keys = array_keys($q['options']);
        $best = array_search($q['answer'], $keys, true);
        $situations[] = [
            'id'          => $id . '-' . ($qi + 1),
            'source_id'   => $q['id'],
            'title'       => $q['prompt'],
            'question'    => 'Tình huống: ' . $t['situation'] . "\n\n" . $q['prompt'],
            'image'       => '',
            'video'       => '',
            'choices'     => array_map(fn ($k) => [
                'text' => $q['options'][$k],
                'explanation' => ($k === $q['answer'] ? 'Phù hợp nhất. ' : 'Chưa phù hợp. ') . $q['explanation'],
            ], $keys),
            'best'        => $best === false ? 0 : $best,
            'status'      => 'Đã công bố',
            'order'       => $qi + 1,
            'allow_retry' => true,
        ];
    }
    $topics[] = [
        'id' => $id, 'source_id' => $t['id'], 'name' => $t['title'], 'icon' => $icon, 'order' => $order + 1,
        'description' => $desc,
        'content' => "Tình huống: {$t['situation']}\n\nKiến thức cốt lõi: {$t['lesson']}\n\nCách ứng xử: {$t['suggested_action']}",
        'situations' => $situations,
    ];
}

// Học liệu KHÔNG lấy từ hồ sơ: bộ câu hỏi trong hồ sơ (mục 7) là bản đề xuất soạn lại.
// Học liệu dùng bộ câu hỏi gốc của nhà trường – nhập bằng tools/nhap-hoc-lieu.php.
// Từ mục 7 chỉ lấy ngưỡng đạt (đúng ≥ 2/3 câu).
$out = [
    'nguon'            => 'Bộ hồ sơ chạy thử Kết nối học sinh – phiên bản ' . ($learn['metadata']['version'] ?? '?') . ', ngày ' . ($learn['metadata']['date'] ?? '?'),
    'pass_threshold'   => (int) ($learn['test_data']['pass_threshold'] ?? 2),
    'staff'            => $admin['test_data']['staff'],
    'student_account'  => array_diff_key($acc['test_data']['student_account'], ['test_password' => 1]), // không chép mật khẩu
    'reports'          => $acc['test_data']['reports'],
    'identities'       => $acc['test_data']['identity_records_restricted'],
];
unset($topics);
$file = dirname(__DIR__) . '/includes/du-lieu-mau.json';
$data = is_file($file) ? (json_decode((string) file_get_contents($file), true) ?: []) : [];
file_put_contents($file, json_encode(array_merge($data, $out), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
printf("Đã cập nhật %s: %d nhân sự, %d phản ánh, %d bản ghi danh tính (giữ nguyên học liệu)\n", $file,
    count($out['staff']), count($out['reports']), count($out['identities']));
