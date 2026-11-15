<?php
class modelAdminCategory {

    const ICONS = array('scissors', 'hand', 'leaf', 'brush', 'lotus', 'sparkle');

    public static function getCategoryList() {
        $db = new Database();
        return $db->getAll(
            "SELECT c.*,
                    (SELECT COUNT(*) FROM services s WHERE s.category_id = c.id) AS services_count,
                    (SELECT COUNT(*) FROM masters m WHERE m.category_id = c.id) AS masters_count
             FROM categories c ORDER BY c.sort_order, c.id"
        );
    }

    public static function getCategoryDetail($id) {
        $db = new Database();
        return $db->getOne("SELECT * FROM categories WHERE id = ?", array((int)$id));
    }

    private static function readForm() {
        $f = array(
            'name_et'    => trim(isset($_POST['name_et']) ? $_POST['name_et'] : ''),
            'name_ru'    => trim(isset($_POST['name_ru']) ? $_POST['name_ru'] : ''),
            'icon'       => isset($_POST['icon']) && in_array($_POST['icon'], self::ICONS, true) ? $_POST['icon'] : 'sparkle',
            'sort_order' => isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0,
        );
        $errors = ($f['name_et'] === '' || $f['name_ru'] === '') ? array('Täida nimetus mõlemas keeles.') : array();
        return array($f, $errors);
    }

    public static function getCategoryAdd() {
        list($f, $errors) = self::readForm();
        if ($errors) {
            return array(false, $errors);
        }
        $db = new Database();
        $db->executeRun("INSERT INTO categories (name_et, name_ru, icon, sort_order) VALUES (?, ?, ?, ?)",
            array($f['name_et'], $f['name_ru'], $f['icon'], $f['sort_order']));
        return array(true, array());
    }

    public static function getCategoryEdit($id) {
        list($f, $errors) = self::readForm();
        if ($errors) {
            return array(false, $errors);
        }
        $db = new Database();
        $db->executeRun("UPDATE categories SET name_et = ?, name_ru = ?, icon = ?, sort_order = ? WHERE id = ?",
            array($f['name_et'], $f['name_ru'], $f['icon'], $f['sort_order'], (int)$id));
        return array(true, array());
    }

    // Kategooriat saab kustutada ainult siis, kui sellel pole teenuseid ega meistreid
    public static function getCategoryDelete($id) {
        $c = self::getCategoryDetail($id);
        if (!$c) {
            return array(false, array('Kategooriat ei leitud.'));
        }
        $db = new Database();
        $used = $db->getOne(
            "SELECT (SELECT COUNT(*) FROM services WHERE category_id = ?) + (SELECT COUNT(*) FROM masters WHERE category_id = ?) AS c",
            array((int)$id, (int)$id)
        );
        if ($used['c'] > 0) {
            return array(false, array('Kategoorias on teenuseid või meistreid — kustuta või liiguta need enne.'));
        }
        $db->executeRun("DELETE FROM categories WHERE id = ?", array((int)$id));
        return array(true, array());
    }
}
