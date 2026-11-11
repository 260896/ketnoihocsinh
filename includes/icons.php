<?php
declare(strict_types=1);

/** Bộ icon nét (stroke) theo bản thiết kế. */
const ICONS = [
    'graduation'   => '<path d="M12 3l9 4.5-9 4.5-9-4.5L12 3z"/><path d="M6 10v5c0 1.7 2.7 3 6 3s6-1.3 6-3v-5"/>',
    'phone'        => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z"/>',
    'shield'       => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
    'vapeOff'      => '<rect x="2" y="13" width="16" height="5" rx="1.5"/><path d="M22 13v5"/><path d="M18 8c0-2 2-2 2-4"/><path d="M3 3l18 18"/>',
    'messageLines' => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><line x1="8" y1="9" x2="16" y2="9"/><line x1="8" y1="13" x2="13" y2="13"/>',
    'eyeOff'       => '<path d="M17.9 17.9A10 10 0 0 1 12 20c-7 0-10-8-10-8a18 18 0 0 1 5.1-5.9"/><path d="M9.9 4.2A9 9 0 0 1 12 4c7 0 10 8 10 8a18 18 0 0 1-2.2 3.2"/><path d="M2 2l20 20"/>',
    'lock'         => '<rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
    'file'         => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/>',
    'user'         => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>',
    'users'        => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/>',
    'book'         => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20V3H6.5A2.5 2.5 0 0 0 4 5.5v14z"/><path d="M20 17v4H6.5A2.5 2.5 0 0 1 4 18.5"/>',
    'bell'         => '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/>',
    'chevronDown'  => '<path d="M6 9l6 6 6-6"/>',
    'chevronLeft'  => '<path d="M15 18l-6-6 6-6"/>',
    'chevronRight' => '<path d="M9 18l6-6-6-6"/>',
    'chevronUp'    => '<path d="M18 15l-6-6-6 6"/>',
    'x'            => '<path d="M18 6L6 18M6 6l12 12"/>',
    'upload'       => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M17 8l-5-5-5 5M12 3v12"/>',
    'plus'         => '<path d="M12 5v14M5 12h14"/>',
    'info'         => '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>',
    'edit'         => '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>',
    'trash'        => '<path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/>',
    'inbox'        => '<path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.5 5.1L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.5-6.9A2 2 0 0 0 16.8 4H7.2a2 2 0 0 0-1.7 1.1z"/>',
    'settings'     => '<circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M4.2 4.2l2.1 2.1M17.7 17.7l2.1 2.1M2 12h3M19 12h3M4.2 19.8l2.1-2.1M17.7 6.3l2.1-2.1"/>',
    'history'      => '<path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="9"/>',
    'clock'        => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
    'logout'       => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>',
    'alert'        => '<path d="M10.3 3.9L1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4M12 17h.01"/>',
    'mail'         => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 6l-10 7L2 6"/>',
    'key'          => '<circle cx="7.5" cy="15.5" r="4.5"/><path d="M10.7 12.3L21 2M18 5l3 3M15 8l2 2"/>',
    'check'        => '<path d="M20 6L9 17l-5-5"/>',
    'send'         => '<path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-4 20-7z"/>',
    'play'         => '<circle cx="12" cy="12" r="10"/><path d="M10 8l6 4-6 4V8z"/>',
    'award'        => '<circle cx="12" cy="8" r="7"/><path d="M8.2 13.9L7 23l5-3 5 3-1.2-9.1"/>',
    'moon'         => '<path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/>',
    'search'       => '<circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/>',
    'paperclip'    => '<path d="M21.4 11.1l-9.2 9.2a6 6 0 0 1-8.5-8.5l9.2-9.2a4 4 0 0 1 5.7 5.7l-9.2 9.2a2 2 0 0 1-2.8-2.8l8.5-8.5"/>',
    'heart'        => '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1.1L12 21l7.8-7.5 1-1.1a5.5 5.5 0 0 0 0-7.8z"/>',
    'hand'         => '<path d="M18 11V6a2 2 0 0 0-4 0v5"/><path d="M14 10V4a2 2 0 0 0-4 0v6"/><path d="M10 10.5V6a2 2 0 0 0-4 0v8"/><path d="M18 8a2 2 0 1 1 4 0v6a8 8 0 0 1-8 8h-2c-2.8 0-4.5-.9-6-2.4l-3.6-3.6a2 2 0 0 1 2.8-2.8L7 15"/>',
    'download'     => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5M12 15V3"/>',
];

/** In icon SVG. $label != '' thì icon có nghĩa (role="img"), ngược lại là trang trí. */
function icon(string $name, int $size = 20, string $color = 'currentColor', string $label = ''): string
{
    $a11y = $label !== '' ? 'role="img" aria-label="' . e($label) . '"' : 'aria-hidden="true"';
    return '<svg class="ic" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . e($color)
        . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" ' . $a11y . '>' . (ICONS[$name] ?? '') . '</svg>';
}
