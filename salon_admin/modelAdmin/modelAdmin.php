<?php
class modelAdmin {

    // Administraatori autentimine: true, false või 'forbidden' (pole admin)
    public static function userAuthentication() {
        if (self::isAdmin()) {
            return true;
        }
        if (!isset($_POST['btnLogin'], $_POST['email'], $_POST['password'])) {
            return false;
        }
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'];
        if (!$email || $password === '') {
            return false;
        }

        $db = new Database();
        $item = $db->getOne("SELECT * FROM users WHERE email = ?", array(strtolower($email)));
        if ($item && password_verify($password, $item['password'])) {
            if ($item['status'] !== 'admin') {
                return 'forbidden';
            }
            session_regenerate_id(true);
            $_SESSION['sessionId'] = session_id();
            $_SESSION['userId']    = $item['id'];
            $_SESSION['name']      = $item['username'];
            $_SESSION['status']    = $item['status'];
            return true;
        }
        return false;
    }

    public static function isLoggedIn() {
        return isset($_SESSION['userId'], $_SESSION['sessionId']);
    }

    public static function isAdmin() {
        return self::isLoggedIn() && isset($_SESSION['status']) && $_SESSION['status'] === 'admin';
    }

    public static function userLogout() {
        unset($_SESSION['sessionId'], $_SESSION['userId'], $_SESSION['name'], $_SESSION['status']);
        session_destroy();
    }

    // Statistika töölaua jaoks
    public static function getStats() {
        $db = new Database();
        return array(
            'services'   => $db->getOne("SELECT COUNT(*) c FROM services")['c'],
            'categories' => $db->getOne("SELECT COUNT(*) c FROM categories")['c'],
            'new'        => $db->getOne("SELECT COUNT(*) c FROM bookings WHERE status = 'new'")['c'],
            'upcoming'   => $db->getOne("SELECT COUNT(*) c FROM bookings WHERE booking_date >= CURDATE() AND status <> 'cancelled'")['c'],
            'reviews'    => $db->getOne("SELECT COUNT(*) c FROM reviews")['c'],
        );
    }
}
