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

    // Broneerimise vorm
    public static function BookingForm($errorKey = null, $old = array()) {
        $categories = Category::getAllCategory();
        $services   = Service::getAllServices();
        $masters    = Master::getAllMasters();
        $selectedService = isset($old['service_id']) ? (int)$old['service_id']
                         : (isset($_GET['service']) ? (int)$_GET['service'] : 0);
        include_once 'view/booking.php';
    }

    // Broneeringu salvestamine (POST)
    public static function BookingSave() {
        $result = Booking::createBooking($_POST);
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
        include_once 'view/bookingDone.php';
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

    //------------------------- REGISTREERIMINE
    public static function registerForm() {
        include_once 'view/formRegister.php';
    }

    public static function registerUser() {
        $result = Register::registerUser();
        include_once 'view/answerRegister.php';
    }
}
