<?php
// Arvustused
class Review {

    public static function getReviewsByServiceID($id) {
        $db = new Database();
        return $db->getAll("SELECT * FROM reviews WHERE service_id = ? ORDER BY id DESC", array((int)$id));
    }

    public static function getLastReviews($limit = 3) {
        $db = new Database();
        return $db->getAll(
            "SELECT r.*, s.title_et, s.title_ru FROM reviews r
             JOIN services s ON s.id = r.service_id
             ORDER BY r.id DESC LIMIT " . (int)$limit
        );
    }

    public static function insertReview($serviceId, $author, $rating, $text) {
        $author = trim($author);
        $text   = trim($text);
        $rating = (int)$rating;
        if ($author === '' || $text === '' || $rating < 1 || $rating > 5 || !Service::getServiceByID($serviceId)) {
            return false;
        }
        $db = new Database();
        return $db->executeRun(
            "INSERT INTO reviews (service_id, author, rating, text) VALUES (?, ?, ?, ?)",
            array((int)$serviceId, mb_substr($author, 0, 100), $rating, mb_substr($text, 0, 1000))
        ) > 0;
    }
}
