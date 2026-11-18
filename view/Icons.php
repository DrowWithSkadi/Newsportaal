<?php
// Lihtsad SVG-ikoonid (joonistatud käsitsi, ilma väliste teekideta)
class Icon {
    private static $paths = array(
        'scissors' => '<circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><path d="M20 4 8.1 15.9M14.5 14.5 20 20M8.1 8.1 12 12"/>',
        'hand'     => '<path d="M8 13V5.5a1.5 1.5 0 0 1 3 0V12"/><path d="M11 11.5V4a1.5 1.5 0 0 1 3 0v7.5"/><path d="M14 11.5V5.5a1.5 1.5 0 0 1 3 0V14"/><path d="M8 13a1.5 1.5 0 0 0-3 0c0 4 2.5 8 7.5 8s6.5-3 6.5-7V9.5a1.5 1.5 0 0 0-3 0"/>',
        'leaf'     => '<path d="M5 21c0-9 5-15 15-16-1 10-7 15-15 16Z"/><path d="M5 21 14 12"/>',
        'brush'    => '<path d="M14 4l6 6-8.5 8.5a3 3 0 0 1-2 .9H6v-3.5a3 3 0 0 1 .9-2Z"/><path d="M12 6l6 6"/><path d="M6 19.4 4 21"/>',
        'lotus'    => '<path d="M12 20c-4 0-8-2-9-6 3 0 6 1 9 4 3-3 6-4 9-4-1 4-5 6-9 6Z"/><path d="M12 18c-2-2-3-5-3-8 1-2 2-4 3-6 1 2 2 4 3 6 0 3-1 6-3 8Z"/>',
        'sparkle'  => '<path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8Z"/><path d="M19 17l.7 1.8 1.8.7-1.8.7L19 22l-.7-1.8-1.8-.7 1.8-.7Z"/>',
        'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'phone'    => '<path d="M5 4h3l2 5-2.5 1.5a11 11 0 0 0 6 6L15 14l5 2v3a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/>',
        'mail'     => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
        'pin'      => '<path d="M12 21s-7-6.5-7-12a7 7 0 0 1 14 0c0 5.5-7 12-7 12Z"/><circle cx="12" cy="9" r="2.5"/>',
        'star'     => '<path d="M12 3.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8L12 16.8l-5.2 2.8 1-5.8-4.3-4.1 5.9-.9Z"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
        'user'     => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>',
        'logout'   => '<path d="M15 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3"/><path d="M10 17l-5-5 5-5M5 12h11"/>',
        'menu'     => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'close'    => '<path d="M6 6l12 12M18 6 6 18"/>',
        'arrow-up' => '<path d="M12 19V5M5 12l7-7 7 7"/>',
        'arrow-right' => '<path d="M5 12h14M13 5l7 7-7 7"/>',
        'search'   => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        'check'    => '<path d="M5 12.5 10 17 19 7"/>',
        'chevron'  => '<path d="m6 9 6 6 6-6"/>',
    );

    public static function svg($name, $class = 'icon') {
        $p = isset(self::$paths[$name]) ? self::$paths[$name] : self::$paths['sparkle'];
        return '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" '
             . 'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $p . '</svg>';
    }

    // Tähehinnang (nt 4 -> ★★★★☆)
    public static function stars($rating) {
        $html = '<span class="stars" aria-label="' . e($rating) . '/5">';
        for ($i = 1; $i <= 5; $i++) {
            $html .= '<span class="' . ($i <= round($rating) ? 'on' : 'off') . '">★</span>';
        }
        return $html . '</span>';
    }
}
