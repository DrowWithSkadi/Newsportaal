<?php
class controllerAdmin {

    public static function formLoginSite() {
        if (modelAdmin::isAdmin()) {
            return self::dashboard();
        }
        include_once 'viewAdmin/formLogin.php';
    }

    // Sisselogimine
    public static function loginAction() {
        $auth = modelAdmin::userAuthentication();
        if ($auth === true) {
            header('Location: dashboard');
            exit;
        }
        $_SESSION['errorString'] = $auth === 'forbidden' ? 'Sul pole administraatori õigusi.' : $auth;
        include_once 'viewAdmin/formLogin.php';
    }

    public static function logoutAction() {
        modelAdmin::userLogout();
        header('Location: ./');
        exit;
    }

    // Töölaud
    public static function dashboard() {
        $stats = modelAdmin::getStats();
        $upcoming = array_slice(modelAdminBooking::getBookingList('', true), 0, 8);
        include_once 'viewAdmin/startAdmin.php';
    }

    public static function error404() {
        http_response_code(404);
        include_once 'viewAdmin/error404.php';
    }
}
