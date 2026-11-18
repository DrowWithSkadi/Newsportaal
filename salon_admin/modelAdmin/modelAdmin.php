<?php
class modelAdmin {

    /**
     * Administraatori autentimine (sama loogika ja lukustus nagu avalikul saidil, vt model/Auth.php).
     * Tagastab true, 'forbidden' (kasutaja pole admin) või veateksti.
     */
    public static function userAuthentication() {
        if (self::isAdmin()) {
            return true;
        }
        if (!isset($_POST['btnLogin'], $_POST['email'], $_POST['password'])) {
            return 'Vale e-post või parool.';
        }
        $result = Auth::attempt($_POST['email'], $_POST['password']);
        if ($result['ok']) {
            return self::isAdmin() ? true : 'forbidden';
        }
        if ($result['error'] === 'login_locked') {
            return 'Liiga palju ebaõnnestunud katseid. Konto on lukustatud ' . $result['minutes'] . ' minutiks.';
        }
        return 'Vale e-post või parool.' . ($result['left'] !== null ? ' Katseid jäänud: ' . $result['left'] . '.' : '');
    }

    public static function isLoggedIn() {
        return isset($_SESSION['userId'], $_SESSION['sessionId']);
    }

    public static function isAdmin() {
        return self::isLoggedIn() && isset($_SESSION['status']) && $_SESSION['status'] === 'admin';
    }

    public static function userLogout() {
        Auth::logout();
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
