<?php
// Teenused
class Service {

    private static $select = "SELECT s.*, c.name_et AS category_et, c.name_ru AS category_ru, c.icon,
                                     ROUND(AVG(r.rating), 1) AS rating, COUNT(r.id) AS reviews_count
                              FROM services s
                              JOIN categories c ON c.id = s.category_id
                              LEFT JOIN reviews r ON r.service_id = s.id";

    public static function getPopularServices($limit = 6) {
        $db = new Database();
        return $db->getAll(self::$select . " WHERE s.is_popular = 1 GROUP BY s.id ORDER BY s.id LIMIT " . (int)$limit);
    }

    public static function getAllServices() {
        $db = new Database();
        return $db->getAll(self::$select . " GROUP BY s.id ORDER BY c.sort_order, s.price");
    }

    public static function getServicesByCategoryID($id) {
        $db = new Database();
        return $db->getAll(self::$select . " WHERE s.category_id = ? GROUP BY s.id ORDER BY s.price", array((int)$id));
    }

    public static function getServiceByID($id) {
        $db = new Database();
        return $db->getOne(self::$select . " WHERE s.id = ? GROUP BY s.id", array((int)$id));
    }

    // Otsing pealkirja ja kirjelduse järgi mõlemas keeles
    public static function searchServices($q) {
        $like = '%' . $q . '%';
        $db = new Database();
        return $db->getAll(
            self::$select . " WHERE s.title_et LIKE ? OR s.title_ru LIKE ? OR s.description_et LIKE ? OR s.description_ru LIKE ?
                              GROUP BY s.id ORDER BY s.price",
            array($like, $like, $like, $like)
        );
    }

    public static function countServices() {
        $db = new Database();
        $row = $db->getOne("SELECT COUNT(*) AS cnt FROM services");
        return (int)$row['cnt'];
    }
}
