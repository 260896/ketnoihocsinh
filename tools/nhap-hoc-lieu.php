<?php
/**
 * Nhập học liệu gốc của nhà trường ("Khởi nghiệp_Tân Hưng") vào dữ liệu mẫu của website.
 *
 *   php tools/nhap-hoc-lieu.php "<thư mục văn bản>" "<thư mục nguồn Khởi nghiệp_Tân Hưng>"
 *
 * <thư mục văn bản>: các tệp .txt đã trích từ .docx (PHP trên XAMPP không có ZipArchive) – xem HUONG-DAN.md mục 4:
 *   Bộ_câu_hỏi_trắc_nghiệm_*.txt và Chu_de_1_*.txt … Chu_de_5_*.txt
 * <thư mục nguồn>: để chép hình minh họa, infographic, video, tài liệu PDF vào data/uploads/.
 *
 * Ghi khóa "topics" và "docs" của includes/du-lieu-mau.json (giữ nguyên các khóa khác).
 * Chỉ chạy từ dòng lệnh.
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Forbidden'); }

$txtDir = rtrim($argv[1] ?? '', '/\\');
$srcDir = rtrim($argv[2] ?? '', '/\\');
$root = dirname(__DIR__);
$find = function (string $dir, string $pattern): ?string {
    foreach (glob($dir . '/*') ?: [] as $f) if (preg_match($pattern, basename($f))) return $f;
    return null;
};

// Chủ đề → id, icon, mô tả ngắn, thư mục ảnh / tên tệp nguồn
$meta = [
    1 => ['bao-luc-hoc-duong', 'shield', 'Nhận biết hành vi bắt nạt, cách ứng xử và hướng tìm kiếm sự hỗ trợ.', '/^1\./u', '/Chu_de_1_/'],
    2 => ['thuoc-la-dien-tu', 'vapeOff', 'Nhận biết tác hại, quy định liên quan và cách từ chối.', '/^2\./u', '/Chu_de_2_/'],
    3 => ['an-toan-mang-xa-hoi', 'messageLines', 'Nhận diện dấu hiệu lừa đảo, bảo vệ dữ liệu cá nhân và cách báo cáo sự việc.', '/^3\./u', '/Chu_de_3_/'],
    4 => ['phong-chong-tao-hon', 'heart', 'Nhận biết tảo hôn, điều kiện tuổi kết hôn, quyền tự nguyện và cách tìm kiếm sự hỗ trợ.', '/^4\./u', '/Chu_de_4_/'],
    5 => ['phong-chong-quay-roi', 'hand', 'Nhận diện quấy rối trực tiếp và trên mạng, tôn trọng ranh giới, bảo vệ bản thân và tìm hỗ trợ.', '/^5\./u', '/Chu_de_5_/'],
];

/* ---------- 1. Bộ câu hỏi trắc nghiệm (15 câu) ---------- */
$quizFile = $find($txtDir, '/^B.*c.u.h.i.*\.txt$/u');
if (!$quizFile) { fwrite(STDERR, "Không thấy tệp bộ câu hỏi trong $txtDir\n"); exit(1); }
$lines = array_values(array_filter(array_map('trim', preg_split('/\R/u', (string) file_get_contents($quizFile))), fn ($l) => $l !== ''));
$topics = [];
$cur = null; $q = null;
$flushQ = function () use (&$cur, &$q) { if ($cur !== null && $q) { $cur['questions'][] = $q; } $q = null; };
foreach ($lines as $l) {
    if (preg_match('/^Chủ đề (\d+):\s*(.+)$/u', $l, $m)) {
        $flushQ(); if ($cur) $topics[] = $cur;
        $cur = ['n' => (int) $m[1], 'name' => $m[2], 'situation' => '', 'learn' => '', 'questions' => []];
    } elseif ($cur && preg_match('/^Tình huống:\s*(.+)$/u', $l, $m)) {
        $cur['situation'] = $m[1];
    } elseif ($cur && preg_match('/^Nội dung học tập:\s*(.+)$/u', $l, $m)) {
        $cur['learn'] = $m[1];
    } elseif ($cur && preg_match('/^Câu (\d+)\.\s*(.+)$/u', $l, $m)) {
        $flushQ(); $q = ['prompt' => $m[2], 'options' => [], 'answer' => '', 'explanation' => ''];
    } elseif ($q && preg_match('/^([A-D])\.\s*(.+)$/u', $l, $m)) {
        $q['options'][$m[1]] = $m[2];
    } elseif ($q && preg_match('/^Đáp án:\s*([A-D])\.\s*(.+)$/u', $l, $m)) {
        $q['answer'] = $m[1]; $q['explanation'] = $m[2];
    }
}
$flushQ(); if ($cur) $topics[] = $cur;

/* ---------- 2. Tài liệu thảo luận chủ đề → nội dung học tập ---------- */
$discussion = function (int $n) use ($txtDir, $find, $meta): string {
    $f = $find($txtDir, $meta[$n][4]);
    if (!$f) return '';
    $ls = array_values(array_filter(array_map('trim', preg_split('/\R/u', (string) file_get_contents($f))), fn ($l) => $l !== ''));
    // Bỏ 2 dòng tiêu đề "CHỦ ĐỀ n" / tên chủ đề; mỗi dòng còn lại là một đoạn
    return implode("\n\n", array_slice($ls, 2));
};

/* ---------- 3. Chép tệp media vào data/uploads ---------- */
$copy = function (?string $from, string $toRel) use ($root): string {
    if (!$from || !is_file($from)) return '';
    $to = $root . '/data/uploads/' . $toRel;
    if (!is_dir(dirname($to))) mkdir(dirname($to), 0775, true);
    copy($from, $to);
    return $toRel;
};

$out = []; $docs = []; $nQ = 0; $nFiles = 0;
foreach ($topics as $t) {
    [$id, $icon, $desc, $mediaPat] = $meta[$t['n']];
    $media = [];
    if ($srcDir) {
        $imgDir = $find($srcDir . '/hình ảnh chủ đề', $mediaPat);
        $photos = $imgDir ? glob($imgDir . '/*') : [];
        foreach ($photos as $i => $p) $media['photos'][] = $copy($p, "images/hoc-lieu/$id-anh-" . ($i + 1) . '.jpg');
        $media['infographic'] = $copy($find($srcDir . '/infographic', $mediaPat), "images/hoc-lieu/$id-infographic.png");
        $media['video'] = $copy($find($srcDir . '/video', '/Chủ đề ' . $t['n'] . ' /u'), "docs/hoc-lieu/$id.mp4");
        $media['pdf'] = $copy($find($srcDir . '/tài liệu', $mediaPat), "docs/hoc-lieu/$id.pdf");
        $nFiles += count(array_filter([...($media['photos'] ?? []), $media['infographic'], $media['video'], $media['pdf']]));
    }
    $situations = [];
    foreach ($t['questions'] as $qi => $qq) {
        $keys = array_keys($qq['options']);
        $situations[] = [
            'id' => "$id-" . ($qi + 1), 'title' => $qq['prompt'],
            'question' => 'Tình huống: ' . $t['situation'] . "\n\n" . $qq['prompt'],
            'image' => $media['photos'][$qi % max(1, count($media['photos'] ?? []))] ?? '', 'video' => '',
            'choices' => array_map(fn ($k) => [
                'text' => $qq['options'][$k],
                'explanation' => ($k === $qq['answer'] ? 'Phù hợp nhất. ' . $qq['explanation'] : 'Chưa phù hợp. Đáp án phù hợp nhất là ' . $qq['answer'] . '. ' . $qq['explanation']),
            ], $keys),
            'best' => (int) array_search($qq['answer'], $keys, true), 'status' => 'Đã công bố', 'order' => $qi + 1, 'allow_retry' => true,
        ];
        $nQ++;
    }
    $out[] = [
        'id' => $id, 'name' => $t['name'], 'icon' => $icon, 'order' => $t['n'], 'description' => $desc,
        'situation' => $t['situation'], 'learn' => $t['learn'],
        'image' => $media['infographic'] ?? '', 'video_file' => $media['video'] ?? '',
        'content' => "Tình huống: {$t['situation']}\n\nNội dung học tập: {$t['learn']}\n\n" . $discussion($t['n']),
        'situations' => $situations,
    ];
    foreach (['pdf' => 'PDF', 'video' => 'Video', 'infographic' => 'Infographic'] as $k => $fmt) {
        if (!empty($media[$k])) {
            $docs[] = ['id' => "d-$id-$k", 'topic_id' => $id, 'name' => ($k === 'pdf' ? 'Tài liệu: ' : ($k === 'video' ? 'Video: ' : 'Infographic: ')) . $t['name'],
                'format' => $fmt, 'path' => $media[$k], 'link' => '', 'status' => 'Đã công bố'];
        }
    }
}

$file = $root . '/includes/du-lieu-mau.json';
$data = is_file($file) ? (json_decode((string) file_get_contents($file), true) ?: []) : [];
$data['topics'] = $out;
$data['docs'] = $docs;
$data['nguon_hoc_lieu'] = 'Bộ câu hỏi trắc nghiệm – Nội dung học tập ban đầu (10/11/2026) và tài liệu thảo luận 5 chủ đề';
file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
printf("Đã ghi %d chủ đề, %d câu hỏi, %d tài liệu; chép %d tệp media vào data/uploads/\n", count($out), $nQ, count($docs), $nFiles);
foreach ($out as $t) printf("  %-22s %d câu · ảnh %s · video %s\n", $t['id'], count($t['situations']), $t['image'] ? 'có' : '—', $t['video_file'] ? 'có' : '—');
