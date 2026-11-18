<?php

class Controller {

    // Avaleht
    public static function StartSite() {
        $categories = Category::getAllCategory();
        $popular    = Service::getPopularServices(6);
        $masters    = array_slice(Master::getAllMasters(), 0, 4);
        $reviews    = Review::getLastReviews(3);
        $servicesCount = Service::countServices();
        include_once 'view/start.php';
    }

    // Kategooriad menüü jaoks
    public static function AllCategory() {
        return Category::getAllCategory();
    }

    // Kõik teenused (filtreerimine JS-iga)
    public static function AllServices() {
        $categories = Category::getAllCategory();
        $arr = Service::getAllServices();
        include_once 'view/services.php';
    }

    // Ühe kategooria teenused
    public static function ServicesByCatID($id) {
        $category = Category::getCategoryByID($id);
        if (!$category) {
            return self::error404();
        }
        $arr = Service::getServicesByCategoryID($id);
        include_once 'view/category.php';
    }

    // Teenuse leht koos arvustustega
    public static function ServiceByID($id) {
        $service = Service::getServiceByID($id);
        if (!$service) {
            return self::error404();
        }
        $reviews = Review::getReviewsByServiceID($id);
        include_once 'view/service.php';
    }

    // Arvustuse lisamine (POST)
    public static function InsertReview() {
        $id = isset($_POST['service_id']) ? (int)$_POST['service_id'] : 0;
        $ok = Review::insertReview(
            $id,
            isset($_POST['author']) ? $_POST['author'] : '',
            isset($_POST['rating']) ? $_POST['rating'] : 0,
            isset($_POST['text']) ? $_POST['text'] : ''
        );
        header('Location: service?id=' . $id . ($ok ? '&review=ok' : '&review=error') . '#reviews');
        exit;
    }

    // Broneerimise vorm (sisselogimata kasutajale näidatakse kutset sisse logida)
    public static function BookingForm($errorKey = null, $old = array()) {
        $user       = Auth::user();
        $categories = Category::getAllCategory();
        $services   = Service::getAllServices();
        $masters    = Master::getAllMasters();
        $selectedService = isset($old['service_id']) ? (int)$old['service_id']
                         : (isset($_GET['service']) ? (int)$_GET['service'] : 0);
        include_once 'view/booking.php';
    }

    // Broneeringu salvestamine (POST) — ainult sisselogitud kasutajale
    public static function BookingSave() {
        $service = isset($_POST['service_id']) ? (int)$_POST['service_id'] : 0;
        self::requireLogin('booking' . ($service ? '?service=' . $service : ''));
        if (!Auth::csrfValid()) {
            return self::BookingForm('csrf_err', $_POST);
        }
        $result = Booking::createBooking($_POST, Auth::id());
        if ($result[0]) {
            $_SESSION['last_booking'] = $result[1];
            header('Location: bookingDone');
            exit;
        }
        return self::BookingForm($result[1], $_POST);
    }

    // Broneeringu kinnitus (näidatakse ainult broneerijale)
    public static function BookingDone() {
        if (!isset($_SESSION['last_booking'])) {
            header('Location: booking');
            exit;
        }
        $booking = Booking::getBookingByID($_SESSION['last_booking']);
        if (!$booking || (int)$booking['user_id'] !== Auth::id()) {
            self::redirect('booking');
        }
        include_once 'view/bookingDone.php';
    }

    // Minu broneeringud: eelseisvad ja möödunud, tühistamise nupuga
    public static function MyBookings() {
        self::requireLogin('myBookings');
        $upcoming = array();
        $past = array();
        foreach (Booking::getUserBookings(Auth::id()) as $b) {
            if (Booking::isUpcoming($b)) {
                array_unshift($upcoming, $b); // lähim ees
            } else {
                $past[] = $b;
            }
        }
        include_once 'view/myBookings.php';
    }

    public static function BookingCancel() {
        self::requireLogin('myBookings');
        $ok = Auth::csrfValid() && Booking::cancelByUser(isset($_POST['id']) ? $_POST['id'] : 0, Auth::id());
        self::flash($ok ? 'ok' : 'error', t($ok ? 'cancel_ok' : 'cancel_err'));
        self::redirect('myBookings');
    }

    // Vabad ajad JSON-ina (AJAX)
    public static function Slots($masterId, $date) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(Booking::getSlotsWithState($masterId, $date));
        exit;
    }

    public static function Masters() {
        $masters = Master::getAllMasters();
        include_once 'view/masters.php';
    }

    public static function Contact() {
        include_once 'view/contact.php';
    }

    public static function Search($q) {
        $q = trim($q);
        $arr = $q === '' ? array() : Service::searchServices($q);
        include_once 'view/search.php';
    }

    public static function error404() {
        http_response_code(404);
        include_once 'view/error404.php';
    }

    //------------------------- ABIFUNKTSIOONID
    // Praegune leht koos parameetritega (tagasisuunamiseks pärast sisselogimist)
    public static function currentPath() {
        $uri = $_SERVER['REQUEST_URI'];
        $last = substr($uri, strrpos(explode('?', $uri)[0], '/') + 1);
        $last = preg_replace('/([?&])lang=[a-z]+&?/', '$1', $last);
        return Auth::safeReturn(rtrim($last, '?&'), '');
    }

    public static function redirect($url) {
        header('Location: ' . $url);
        exit;
    }

    // Ühekordne teade järgmisel lehel
    public static function flash($type, $text) {
        $_SESSION['flash'] = array('type' => $type, 'text' => $text);
    }

    public static function takeFlash() {
        if (!isset($_SESSION['flash'])) {
            return null;
        }
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }

    // Kui kasutaja pole sisse logitud, suunatakse sisselogimisele ja pärast tagasi
    public static function requireLogin($return) {
        if (!Auth::check()) {
            self::flash('error', t('login_required'));
            self::redirect('login?return=' . urlencode($return));
        }
    }

    //------------------------- SISSELOGIMINE
    public static function loginForm($error = '', $email = '') {
        if (Auth::check()) {
            self::redirect('./');
        }
        $return = Auth::safeReturn(isset($_REQUEST['return']) ? $_REQUEST['return'] : '', '');
        include_once 'view/formLogin.php';
    }

    public static function loginAction() {
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        if (!Auth::csrfValid()) {
            return self::loginForm(t('csrf_err'), $email);
        }
        $result = Auth::attempt($email, isset($_POST['password']) ? $_POST['password'] : '');
        if ($result['ok']) {
            $user = Auth::user();
            self::flash('ok', sprintf(t('welcome_back'), $user['name']));
            self::redirect(Auth::safeReturn(isset($_POST['return']) ? $_POST['return'] : ''));
        }
        if ($result['error'] === 'login_locked') {
            $error = sprintf(t('login_locked'), $result['minutes']);
        } else {
            $error = t('login_err') . ($result['left'] !== null ? ' ' . sprintf(t('login_left'), $result['left']) : '');
        }
        return self::loginForm($error, $email);
    }

    public static function logoutAction() {
        if (Auth::csrfValid()) {
            Auth::logout();
            self::flash('ok', t('logged_out'));
        }
        self::redirect('./');
    }

    //------------------------- REGISTREERIMINE
    public static function registerForm() {
        if (Auth::check()) {
            self::redirect('./');
        }
        $return = Auth::safeReturn(isset($_GET['return']) ? $_GET['return'] : '', '');
        include_once 'view/formRegister.php';
    }

    // Pärast edukat registreerimist logitakse kasutaja kohe sisse
    public static function registerUser() {
        $result = Register::registerUser();
        if ($result[0] == true) {
            self::flash('ok', t('reg_ok'));
            self::redirect(Auth::safeReturn(isset($_POST['return']) ? $_POST['return'] : ''));
        }
        include_once 'view/answerRegister.php';
    }
}
