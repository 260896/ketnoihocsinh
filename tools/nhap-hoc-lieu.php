<?php
/**
 * Nhập học liệu gốc của nhà trường từ thư mục nguon/ vào dữ liệu mẫu của website (includes/du-lieu-mau.json).
 *
 *   php tools/nhap-hoc-lieu.php
 *
 * Đọc:
 *   nguon/hoc-lieu/bo-cau-hoi-trac-nghiem.txt        – 15 câu hỏi (trích từ .docx cùng tên)
 *   nguon/hoc-lieu/thao-luan/chu-de-N-<id>.txt         – nội dung học tập (tài liệu thảo luận chủ đề)
 *   nguon/hoc-lieu/tai-lieu-tham-khao.txt             – danh mục tài liệu tham khảo
 *   nguon/hoc-lieu/<id>/ anh-*.jpg, infographic.png, video.mp4, tai-lieu.pdf
 * Ghi khóa "topics", "docs", "references" (giữ nguyên các khóa khác). Media được website tự chép
 * vào data/uploads/ khi tạo dữ liệu (xem seed_ensure_media() trong includes/seed.php).
 *
 * Khi sửa tệp .docx gốc: lưu lại bản .txt cùng tên (Word → Lưu thành → Văn bản thuần, UTF-8) rồi chạy lại công cụ này.
 * PHP của XAMPP không có ZipArchive nên không đọc thẳng .docx. Chỉ chạy từ dòng lệnh.
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Forbidden'); }

$root = dirname(__DIR__);
$src  = $root . '/nguon/hoc-lieu';
$read = function (string $f): array {
    if (!is_file($f)) { fwrite(STDERR, "Không thấy $f\n"); exit(1); }
    return array_values(array_filter(array_map('trim', preg_split('/\R/u', (string) file_get_contents($f))), fn ($l) => $l !== ''));
};

// Thứ tự chủ đề trong bộ câu hỏi → id, icon, mô tả ngắn
const META = [
    1 => ['bao-luc-hoc-duong', 'shield', 'Nhận biết hành vi bắt nạt, cách ứng xử và hướng tìm kiếm sự hỗ trợ.'],
    2 => ['thuoc-la-dien-tu', 'vapeOff', 'Nhận biết tác hại, quy định liên quan và cách từ chối.'],
    3 => ['an-toan-mang-xa-hoi', 'messageLines', 'Nhận diện dấu hiệu lừa đảo, bảo vệ dữ liệu cá nhân và cách báo cáo sự việc.'],
    4 => ['phong-chong-tao-hon', 'heart', 'Nhận biết tảo hôn, điều kiện tuổi kết hôn, quyền tự nguyện và cách tìm kiếm sự hỗ trợ.'],
    5 => ['phong-chong-quay-roi', 'hand', 'Nhận diện quấy rối trực tiếp và trên mạng, tôn trọng ranh giới, bảo vệ bản thân và tìm hỗ trợ.'],
];

/* ---------- 1. Bộ câu hỏi trắc nghiệm ---------- */
$topics = []; $cur = null; $q = null;
$flushQ = function () use (&$cur, &$q) { if ($cur !== null && $q) $cur['questions'][] = $q; $q = null; };
foreach ($read("$src/bo-cau-hoi-trac-nghiem.txt") as $l) {
    if (preg_match('/^Chủ đề (\d+):\s*(.+)$/u', $l, $m)) {
        $flushQ(); if ($cur) $topics[] = $cur;
        $cur = ['n' => (int) $m[1], 'name' => $m[2], 'situation' => '', 'learn' => '', 'questions' => []];
    } elseif ($cur && preg_match('/^Tình huống:\s*(.+)$/u', $l, $m)) { $cur['situation'] = $m[1];
    } elseif ($cur && preg_match('/^Nội dung học tập:\s*(.+)$/u', $l, $m)) { $cur['learn'] = $m[1];
    } elseif ($cur && preg_match('/^Câu \d+\.\s*(.+)$/u', $l, $m)) { $flushQ(); $q = ['prompt' => $m[1], 'options' => [], 'answer' => '', 'explanation' => ''];
    } elseif ($q && preg_match('/^([A-D])\.\s*(.+)$/u', $l, $m)) { $q['options'][$m[1]] = $m[2];
    } elseif ($q && preg_match('/^Đáp án:\s*([A-D])\.\s*(.+)$/u', $l, $m)) { $q['answer'] = $m[1]; $q['explanation'] = $m[2]; }
}
$flushQ(); if ($cur) $topics[] = $cur;

$out = []; $docs = []; $nQ = 0;
foreach ($topics as $t) {
    [$id, $icon, $desc] = META[$t['n']];
    $dir = "$src/$id";
    // Media: đường dẫn trong data/uploads/ mà website dùng (seed_ensure_media() chép từ nguon/)
    $photos = array_map(fn ($f) => "images/hoc-lieu/$id-" . basename($f), glob("$dir/anh-*.jpg") ?: []);
    $has = fn (string $f) => is_file("$dir/$f");
    $disc = glob("$src/thao-luan/chu-de-{$t['n']}-*.txt")[0] ?? null;
    $situations = [];
    foreach ($t['questions'] as $qi => $qq) {
        $keys = array_keys($qq['options']);
        if (!$qq['answer'] || count($keys) < 2) { fwrite(STDERR, "Câu hỏi thiếu đáp án: {$qq['prompt']}\n"); exit(1); }
        $situations[] = [
            'id' => "$id-" . ($qi + 1), 'title' => $qq['prompt'],
            'question' => 'Tình huống: ' . $t['situation'] . "\n\n" . $qq['prompt'],
            'image' => $photos ? $photos[$qi % count($photos)] : '', 'video' => '',
            'choices' => array_map(fn ($k) => [
                'text' => $qq['options'][$k],
                'explanation' => $k === $qq['answer'] ? 'Phù hợp nhất. ' . $qq['explanation'] : 'Chưa phù hợp. Đáp án phù hợp nhất là ' . $qq['answer'] . '. ' . $qq['explanation'],
            ], $keys),
            'best' => (int) array_search($qq['answer'], $keys, true), 'status' => 'Đã công bố', 'order' => $qi + 1, 'allow_retry' => true,
        ];
        $nQ++;
    }
    $out[] = [
        'id' => $id, 'name' => $t['name'], 'icon' => $icon, 'order' => $t['n'], 'description' => $desc,
        'situation' => $t['situation'], 'learn' => $t['learn'],
        'content' => "Tình huống: {$t['situation']}\n\nNội dung học tập: {$t['learn']}" . ($disc ? "\n\n" . implode("\n\n", array_slice($read($disc), 2)) : ''),
        'situations' => $situations,
    ];
    foreach ([['tai-lieu.pdf', 'PDF', 'Tài liệu', "docs/hoc-lieu/$id.pdf"], ['video.mp4', 'Video', 'Video', "docs/hoc-lieu/$id.mp4"], ['infographic.png', 'Hình ảnh', 'Infographic', "images/hoc-lieu/$id-infographic.png"]] as [$file, $fmt, $label, $path]) {
        if ($has($file)) $docs[] = ['id' => "d-$id-" . strtolower(pathinfo($file, PATHINFO_FILENAME) === 'tai-lieu' ? 'pdf' : ($fmt === 'Video' ? 'video' : 'infographic')),
            'topic_id' => $id, 'name' => "$label: {$t['name']}", 'format' => $fmt, 'path' => $path, 'link' => '', 'status' => 'Đã công bố'];
    }
}

/* ---------- 2. Danh mục tài liệu tham khảo ---------- */
$refs = []; $section = '';
foreach ($read("$src/tai-lieu-tham-khao.txt") as $l) {
    if (preg_match('/^(I|II|III|IV)\s+(.+)$/u', $l, $m)) { $section = $m[2]; continue; }
    if (preg_match('/^(\d+)\.\s*(.+)$/u', $l, $m)) { $refs[] = ['section' => $section, 'n' => (int) $m[1], 'text' => $m[2], 'url' => '']; continue; }
    if (preg_match('#^https?://\S+$#', $l) && $refs) { $refs[count($refs) - 1]['url'] = $l; }
}

$file = $root . '/includes/du-lieu-mau.json';
$data = is_file($file) ? (json_decode((string) file_get_contents($file), true) ?: []) : [];
$data['topics'] = $out;
$data['docs'] = $docs;
$data['references'] = $refs;
$data['nguon_hoc_lieu'] = 'Bộ câu hỏi trắc nghiệm – Nội dung học tập ban đầu (10/11/2026), tài liệu thảo luận 5 chủ đề, danh mục tài liệu tham khảo';
file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
printf("Đã ghi %d chủ đề, %d câu hỏi, %d tài liệu, %d tài liệu tham khảo vào includes/du-lieu-mau.json\n", count($out), $nQ, count($docs), count($refs));
