<?php
// Arvustuste kuvamine
class ViewReview {

    public static function ReviewForm($serviceId) {
        $html  = '<form class="form review-form" method="POST" action="insertreview">';
        $html .= '<h3>' . e(t('review_form_title')) . '</h3>';
        $html .= '<input type="hidden" name="service_id" value="' . (int)$serviceId . '">';
        $html .= '<label>' . e(t('review_name')) . '<input type="text" name="author" maxlength="100" required></label>';
        $html .= '<fieldset class="rating-input"><legend>' . e(t('review_rating')) . '</legend>';
        for ($i = 5; $i >= 1; $i--) {
            $html .= '<input type="radio" id="r' . $i . '" name="rating" value="' . $i . '"' . ($i == 5 ? ' checked' : '') . '>'
                   . '<label for="r' . $i . '" title="' . $i . '">★</label>';
        }
        $html .= '</fieldset>';
        $html .= '<label>' . e(t('review_text')) . '<textarea name="text" rows="4" maxlength="1000" required></textarea></label>';
        $html .= '<button class="btn" type="submit">' . e(t('review_send')) . '</button></form>';
        return $html;
    }

    public static function ReviewsByService($arr) {
        if (!$arr) {
            return '<p class="muted">' . e(t('no_reviews')) . '</p>';
        }
        $html = '<div class="reviews-list">';
        foreach ($arr as $r) {
            $html .= self::Review($r);
        }
        return $html . '</div>';
    }

    public static function Review($r, $withService = false) {
        $html  = '<blockquote class="review reveal">';
        $html .= Icon::stars($r['rating']);
        $html .= '<p>' . nl2br(e($r['text'])) . '</p>';
        $html .= '<footer><strong>' . e($r['author']) . '</strong>';
        if ($withService) {
            $html .= ' · <a href="service?id=' . (int)$r['service_id'] . '">' . e(Lang::field($r, 'title')) . '</a>';
        }
        $html .= ' <time>' . e(date('d.m.Y', strtotime($r['created_at']))) . '</time></footer>';
        return $html . '</blockquote>';
    }
}
