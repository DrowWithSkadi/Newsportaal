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

    // Kas kasutaja on sellele teenusele juba arvustuse jätnud
    public static function userHasReview($serviceId, $userId) {
        $db = new Database();
        return (bool)$db->getOne(
            "SELECT id FROM reviews WHERE service_id = ? AND user_id = ?",
            array((int)$serviceId, (int)$userId)
        );
    }

    /**
     * Arvustuse lisamine sisselogitud kasutaja nimel.
     * Tagastab 'ok', 'error' (vigased andmed) või 'dup' (arvustus juba olemas).
     */
    public static function insertReview($serviceId, $user, $rating, $text) {
        $text   = trim($text);
        $rating = (int)$rating;
        if (!$user || $text === '' || $rating < 1 || $rating > 5 || !Service::getServiceByID($serviceId)) {
            return 'error';
        }
        if (self::userHasReview($serviceId, $user['id'])) {
            return 'dup';
        }
        $db = new Database();
        try {
            $db->executeRun(
                "INSERT INTO reviews (service_id, user_id, author, rating, text) VALUES (?, ?, ?, ?, ?)",
                array((int)$serviceId, (int)$user['id'], mb_substr($user['name'], 0, 100), $rating, mb_substr($text, 0, 1000))
            );
        } catch (PDOException $e) {
            return 'dup'; // unikaalne võti (service_id, user_id)
        }
        return 'ok';
    }
}
