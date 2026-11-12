<?php
// Teenuste kuvamise meetodid
class ViewService {

    // Teenuse pilt või kategooria ikooniga gradient, kui pilti pole
    public static function Picture($s, $class = 'card-media') {
        $tone = 'tone-' . (((int)$s['category_id'] - 1) % 5 + 1);
        if (!empty($s['picture'])) {
            return '<div class="' . $class . ' ' . $tone . '"><img src="' . e($s['picture']) . '" alt="'
                 . e(Lang::field($s, 'title')) . '" loading="lazy"></div>';
        }
        return '<div class="' . $class . ' ' . $tone . '">' . Icon::svg($s['icon'], 'media-icon') . '</div>';
    }

    // Üks teenuse kaart
    public static function Card($s) {
        $title = e(Lang::field($s, 'title'));
        $html  = '<article class="card service-card reveal" data-category="' . (int)$s['category_id'] . '">';
        $html .= '<a href="service?id=' . (int)$s['id'] . '" class="card-link" aria-label="' . $title . '">';
        $html .= self::Picture($s);
        if (!empty($s['is_popular'])) {
            $html .= '<span class="badge">' . e(t('popular')) . '</span>';
        }
        $html .= '</a><div class="card-body">';
        $html .= '<span class="card-cat">' . e(Lang::field($s, 'category')) . '</span>';
        $html .= '<h3><a href="service?id=' . (int)$s['id'] . '">' . $title . '</a></h3>';
        if ((int)$s['reviews_count'] > 0) {
            $html .= '<div class="card-rating">' . Icon::stars($s['rating']) . ' <small>(' . (int)$s['reviews_count'] . ')</small></div>';
        }
        $html .= '<div class="card-meta"><span>' . Icon::svg('clock') . ' ' . (int)$s['duration_min'] . ' ' . e(t('min')) . '</span>';
        $html .= '<strong class="price">' . self::Price($s['price']) . '</strong></div>';
        $html .= '<a class="btn btn-small" href="booking?service=' . (int)$s['id'] . '">' . e(t('book_now')) . '</a>';
        $html .= '</div></article>';
        return $html;
    }

    // Kaartide võrgustik
    public static function Grid($arr) {
        $html = '<div class="grid services-grid">';
        foreach ($arr as $s) {
            $html .= self::Card($s);
        }
        return $html . '</div>';
    }

    public static function Price($price) {
        $p = (float)$price;
        return (floor($p) == $p ? number_format($p, 0) : number_format($p, 2, ',', '')) . ' €';
    }

    // Kategooria kaart avalehel
    public static function CategoryCard($c) {
        return '<a class="cat-card reveal tone-' . (((int)$c['id'] - 1) % 5 + 1) . '" href="category?id=' . (int)$c['id'] . '">'
             . '<span class="cat-icon">' . Icon::svg($c['icon']) . '</span>'
             . '<span class="cat-name">' . e(Lang::field($c, 'name')) . '</span>'
             . '<small>' . (int)$c['services_count'] . ' ' . e(t('services_count')) . '</small></a>';
    }

    // Meistri kaart
    public static function MasterCard($m) {
        $parts = preg_split('/\s+/', $m['name']);
        $initials = mb_substr($parts[0], 0, 1) . (isset($parts[1]) ? mb_substr($parts[1], 0, 1) : '');
        return '<article class="master-card reveal">'
             . '<div class="avatar tone-' . (((int)$m['category_id'] - 1) % 5 + 1) . '">' . e($initials) . '</div>'
             . '<h3>' . e($m['name']) . '</h3>'
             . '<span class="card-cat">' . e(Lang::field($m, 'category')) . '</span>'
             . '<p>' . e(Lang::field($m, 'bio')) . '</p>'
             . '<small class="muted">' . (int)$m['experience'] . ' ' . e(t('years_exp')) . '</small>'
             . '</article>';
    }
}
