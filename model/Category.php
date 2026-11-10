<?php
// Teenuste kategooriad
class Category {

    public static function getAllCategory() {
        $db = new Database();
        return $db->getAll(
            "SELECT c.*, COUNT(s.id) AS services_count
             FROM categories c
             LEFT JOIN services s ON s.category_id = c.id
             GROUP BY c.id
             ORDER BY c.sort_order, c.id"
        );
    }

    public static function getCategoryByID($id) {
        $db = new Database();
        return $db->getOne("SELECT * FROM categories WHERE id = ?", array((int)$id));
    }
}
