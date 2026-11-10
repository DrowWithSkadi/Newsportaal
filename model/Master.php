<?php
// Meistrid
class Master {

    public static function getAllMasters() {
        $db = new Database();
        return $db->getAll(
            "SELECT m.*, c.name_et AS category_et, c.name_ru AS category_ru, c.icon
             FROM masters m JOIN categories c ON c.id = m.category_id
             ORDER BY c.sort_order, m.name"
        );
    }

    public static function getMasterByID($id) {
        $db = new Database();
        return $db->getOne("SELECT * FROM masters WHERE id = ?", array((int)$id));
    }
}
