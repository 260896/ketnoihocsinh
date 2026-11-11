<?php
declare(strict_types=1);

/* Các thành phần giao diện dùng lại – mỗi hàm TRẢ VỀ chuỗi HTML. */

function attrs(array $a): string
{
    $out = '';
    foreach ($a as $k => $v) {
        if ($v === false || $v === null) continue;
        $out .= $v === true ? ' ' . e($k) : ' ' . e($k) . '="' . e($v) . '"';
    }
    return $out;
}

function field_id(string $name): string
{
    return 'f-' . preg_replace('/[^a-z0-9]+/i', '-', $name);
}

function field_error(?string $error, string $id): string
{
    if (!$error) return '';
    return '<div class="field__error" id="' . e($id) . '-err">' . icon('alert', 16, 'var(--c-danger)') . '<p>' . e($error) . '</p></div>';
}

/** Ô nhập một dòng có nhãn. */
function field_input(string $name, string $label, string $value = '', array $opts = []): string
{
    $id = $opts['id'] ?? field_id($name);
    $err = $opts['error'] ?? null;
    unset($opts['id'], $opts['error']);
    $input = '<input' . attrs($opts + [
        'id' => $id, 'name' => $name, 'type' => 'text', 'value' => $value,
        'class' => 'input' . ($err ? ' input--error' : ''),
        'aria-invalid' => $err ? 'true' : null, 'aria-describedby' => $err ? $id . '-err' : null,
    ]) . '>';
    $lbl = $label !== '' ? '<label for="' . e($id) . '" class="field__label">' . e($label) . '</label>' : '';
    return '<div class="field">' . $lbl . $input . field_error($err, $id) . '</div>';
}

function field_textarea(string $name, string $label, string $value = '', array $opts = []): string
{
    $id = $opts['id'] ?? field_id($name);
    $err = $opts['error'] ?? null;
    unset($opts['id'], $opts['error']);
    return '<div class="field"><label for="' . e($id) . '" class="field__label">' . e($label) . '</label>'
        . '<textarea' . attrs($opts + ['id' => $id, 'name' => $name, 'rows' => 4, 'class' => 'textarea' . ($err ? ' input--error' : '')]) . '>'
        . e($value) . '</textarea>' . field_error($err, $id) . '</div>';
}

/**
 * Ô chọn. $options: ['giá trị' => 'nhãn'] hoặc danh sách nhãn (giá trị = nhãn).
 * $opts['sr'] = true để ẩn nhãn (vẫn đọc được bằng trình đọc màn hình).
 */
function field_select(string $name, string $label, array $options, string $selected = '', array $opts = []): string
{
    $id = $opts['id'] ?? field_id($name);
    $sr = !empty($opts['sr']);
    unset($opts['id'], $opts['sr']);
    if (array_is_list($options)) {
        $options = array_combine($options, $options);
    }
    $html = '';
    foreach ($options as $v => $l) {
        $html .= '<option value="' . e($v) . '"' . ((string) $v === $selected ? ' selected' : '') . '>' . e($l) . '</option>';
    }
    return '<div class="field"><label for="' . e($id) . '" class="' . ($sr ? 'sr-only' : 'field__label') . '">' . e($label) . '</label>'
        . '<div class="select-wrap"><select' . attrs($opts + ['id' => $id, 'name' => $name, 'class' => 'select']) . '>' . $html . '</select>'
        . '<span class="select-wrap__icon">' . icon('chevronDown', 18, 'var(--c-muted)') . '</span></div></div>';
}

/** Công tắc bật/tắt (checkbox được tạo kiểu). */
function switch_row(string $name, string $title, string $hint, bool $checked): string
{
    $id = field_id($name);
    return '<div class="row gap-12"><label for="' . e($id) . '" class="grow" style="cursor:pointer"><span class="t-small t-strong d-block">' . e($title) . '</span>'
        . ($hint ? '<span class="t-small t-muted">' . e($hint) . '</span>' : '') . '</label>'
        . '<input type="checkbox" role="switch" class="switch" id="' . e($id) . '" name="' . e($name) . '" value="1"' . ($checked ? ' checked' : '') . '></div>';
}

/** Thẻ lựa chọn dạng radio. */
function choice_radio(string $name, string $value, string $title, string $hint = '', bool $checked = false): string
{
    return '<label class="choice"><input type="radio" class="choice__input" name="' . e($name) . '" value="' . e($value) . '"'
        . ($checked ? ' checked' : '') . ' required><span class="choice__radio"></span><span class="stack gap-2"><span class="t-title">'
        . e($title) . '</span>' . ($hint ? '<span class="t-small t-muted">' . e($hint) . '</span>' : '') . '</span></label>';
}

function dropzone(string $name, string $title, string $hint = '', array $opts = []): string
{
    $size = $opts['icon'] ?? 22;
    unset($opts['icon']);
    return '<label class="dropzone">' . icon('upload', $size, 'var(--c-primary)')
        . '<span class="t-small t-strong t-primary">' . e($title) . '</span>'
        . ($hint ? '<span class="t-small t-muted">' . e($hint) . '</span>' : '')
        . '<input' . attrs($opts + ['type' => 'file', 'name' => $name, 'class' => 'dropzone__input']) . '></label>';
}

function note(string $iconName, string $html, string $color = 'var(--c-primary)'): string
{
    return '<div class="note">' . icon($iconName, 18, $color) . '<p>' . $html . '</p></div>';
}

/** Tên chữ thay cho logo (giai đoạn chạy thử chưa có logo chính thức). */
function wordmark(string $variant = ''): string
{
    return '<span class="wordmark' . ($variant ? ' wordmark--' . e($variant) : '') . '" aria-label="' . e(SITE['project_name']) . '">'
        . '<span class="wordmark__a">' . e(SITE['name_first']) . '</span> <span class="wordmark__b">' . e(SITE['name_second']) . '</span></span>';
}

function brand(bool $withSlogan = true): string
{
    return '<div class="brand stack gap-2">' . wordmark()
        . ($withSlogan ? '<span class="brand__slogan">' . e(SITE['slogan']) . '</span>' : '') . '</div>';
}

/** Dòng ghi chú bản chạy thử. */
function demo_notice(): string
{
    return SITE['is_demo'] ? '<p class="demo-notice">Bản chạy thử – toàn bộ tên trường, nhân sự, email, số điện thoại và dữ liệu là giả lập.</p>' : '';
}

function emergency_card(): string
{
    return '<a href="' . e(tel_href()) . '" class="sos"><span class="icon-circle" style="width:40px;height:40px;background:var(--c-danger)">'
        . icon('phone', 20, '#fff') . '</span><span class="stack gap-2"><span class="t-title t-danger">Cần hỗ trợ ngay?</span>'
        . '<span class="t-small">Gọi ' . e(hotline()) . ' · Trực ' . e(duty_label()) . '</span></span></a>';
}

function status_badge(string $status): string
{
    $cls = [
        'Đã tiếp nhận' => 'badge--info', 'Cần bổ sung' => 'badge--danger',
        'Đang xem xét' => 'badge--neutral', 'Đã có phản hồi' => 'badge--solid',
    ][$status] ?? '';
    return '<span class="badge badge--lg ' . $cls . '">' . e($status) . '</span>';
}

function priority_badge(string $p): string
{
    $cls = [PRIORITY_NOW => 'badge--danger-solid', PRIORITY_LATER => 'badge--muted'][$p] ?? '';
    return '<span class="badge badge--square ' . $cls . '">' . e($p) . '</span>';
}

function publish_badge(string $s): string
{
    return '<span class="badge ' . ($s === 'Đã công bố' ? 'badge--solid' : 'badge--neutral') . '">' . e($s) . '</span>';
}

/** Một nút gửi form POST nhỏ gọn (vd. "Công bố", "Khóa"). */
function post_button(string $label, array $fields, string $class = 'link', string $action = ''): string
{
    $h = '<form method="post" class="inline-form"' . ($action ? ' action="' . e($action) . '"' : '') . '>' . csrf_field();
    foreach ($fields as $k => $v) {
        $h .= '<input type="hidden" name="' . e($k) . '" value="' . e($v) . '">';
    }
    return $h . '<button type="submit" class="' . e($class) . '">' . e($label) . '</button></form>';
}

/** Chữ cái A, B, C… theo vị trí. */
function letter(int $i): string
{
    return chr(65 + $i);
}
