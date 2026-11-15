<?php
class modelAdminService {

    const UPLOAD_DIR = '../uploads/';

    public static function getServiceList() {
        $db = new Database();
        return $db->getAll(
            "SELECT s.*, c.name_et AS category_et, c.icon
             FROM services s JOIN categories c ON c.id = s.category_id
             ORDER BY c.sort_order, s.title_et"
        );
    }

    public static function getServiceDetail($id) {
        $db = new Database();
        return $db->getOne("SELECT * FROM services WHERE id = ?", array((int)$id));
    }

    // Vormi andmete kontroll; tagastab array(andmed, vead)
    private static function readForm() {
        $f = array();
        foreach (array('title_et', 'title_ru', 'description_et', 'description_ru') as $k) {
            $f[$k] = trim(isset($_POST[$k]) ? $_POST[$k] : '');
        }
        $f['price']        = isset($_POST['price']) ? str_replace(',', '.', $_POST['price']) : '';
        $f['duration_min'] = isset($_POST['duration_min']) ? (int)$_POST['duration_min'] : 0;
        $f['category_id']  = isset($_POST['idCategory']) ? (int)$_POST['idCategory'] : 0;
        $f['is_popular']   = isset($_POST['is_popular']) ? 1 : 0;

        $errors = array();
        if ($f['title_et'] === '' || $f['title_ru'] === '' || $f['description_et'] === '' || $f['description_ru'] === '') {
            $errors[] = 'Täida pealkiri ja kirjeldus mõlemas keeles.';
        }
        if (!is_numeric($f['price']) || $f['price'] <= 0) {
            $errors[] = 'Hind peab olema positiivne arv.';
        }
        if ($f['duration_min'] < 5 || $f['duration_min'] > 480) {
            $errors[] = 'Kestus peab olema 5–480 minutit.';
        }
        if (!modelAdminCategory::getCategoryDetail($f['category_id'])) {
            $errors[] = 'Vali kategooria.';
        }
        return array($f, $errors);
    }

    /**
     * Pildi üleslaadimine kausta uploads/.
     * Tagastab tee (nt uploads/abc.jpg), null kui faili pole, või false vea korral.
     */
    private static function uploadPicture() {
        if (!isset($_FILES['picture']) || $_FILES['picture']['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        $file = $_FILES['picture'];
        if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 3 * 1024 * 1024) {
            return false;
        }
        $types = array('image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp');
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!isset($types[$mime])) {
            return false;
        }
        if (!is_dir(self::UPLOAD_DIR)) {
            mkdir(self::UPLOAD_DIR, 0775, true);
        }
        $name = bin2hex(random_bytes(8)) . '.' . $types[$mime];
        if (!move_uploaded_file($file['tmp_name'], self::UPLOAD_DIR . $name)) {
            return false;
        }
        return 'uploads/' . $name;
    }

    private static function removePicture($path) {
        if ($path && strpos($path, 'uploads/') === 0 && is_file('../' . $path)) {
            unlink('../' . $path);
        }
    }

    public static function getServiceAdd() {
        list($f, $errors) = self::readForm();
        $picture = self::uploadPicture();
        if ($picture === false) {
            $errors[] = 'Pilt peab olema JPG, PNG või WEBP ja kuni 3 MB.';
        }
        if ($errors) {
            self::removePicture($picture);
            return array(false, $errors, $f);
        }
        $db = new Database();
        $db->executeRun(
            "INSERT INTO services (category_id, title_et, title_ru, description_et, description_ru, price, duration_min, picture, is_popular)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            array($f['category_id'], $f['title_et'], $f['title_ru'], $f['description_et'], $f['description_ru'],
                  $f['price'], $f['duration_min'], $picture, $f['is_popular'])
        );
        return array(true, array(), $f);
    }

    public static function getServiceEdit($id) {
        $old = self::getServiceDetail($id);
        if (!$old) {
            return array(false, array('Teenust ei leitud.'), array());
        }
        list($f, $errors) = self::readForm();
        $picture = self::uploadPicture();
        if ($picture === false) {
            $errors[] = 'Pilt peab olema JPG, PNG või WEBP ja kuni 3 MB.';
        }
        if ($errors) {
            self::removePicture($picture);
            return array(false, $errors, $f + array('id' => $id, 'picture' => $old['picture']));
        }
        if ($picture === null && isset($_POST['remove_picture'])) {
            self::removePicture($old['picture']);
            $newPicture = null;
        } elseif ($picture === null) {
            $newPicture = $old['picture'];
        } else {
            self::removePicture($old['picture']);
            $newPicture = $picture;
        }
        $db = new Database();
        $db->executeRun(
            "UPDATE services SET category_id = ?, title_et = ?, title_ru = ?, description_et = ?, description_ru = ?,
                    price = ?, duration_min = ?, picture = ?, is_popular = ? WHERE id = ?",
            array($f['category_id'], $f['title_et'], $f['title_ru'], $f['description_et'], $f['description_ru'],
                  $f['price'], $f['duration_min'], $newPicture, $f['is_popular'], (int)$id)
        );
        return array(true, array(), $f);
    }

    public static function getServiceDelete($id) {
        $old = self::getServiceDetail($id);
        if (!$old) {
            return false;
        }
        $db = new Database();
        $ok = $db->executeRun("DELETE FROM services WHERE id = ?", array((int)$id)) > 0;
        if ($ok) {
            self::removePicture($old['picture']);
        }
        return $ok;
    }
}
