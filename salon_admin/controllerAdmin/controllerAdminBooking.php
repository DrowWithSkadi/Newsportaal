<?php
class controllerAdminBooking {

    public static function BookingList() {
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        $upcoming = isset($_GET['upcoming']);
        $arr = modelAdminBooking::getBookingList($status, $upcoming);
        $statuses = modelAdminBooking::STATUSES;
        include_once 'viewAdmin/bookingList.php';
    }

    public static function BookingStatus() {
        modelAdminBooking::setStatus(isset($_POST['id']) ? $_POST['id'] : 0, isset($_POST['status']) ? $_POST['status'] : '');
        self::back();
    }

    public static function BookingDelete() {
        modelAdminBooking::deleteBooking(isset($_POST['id']) ? $_POST['id'] : 0);
        self::back();
    }

    private static function back() {
        $q = isset($_POST['back']) ? $_POST['back'] : '';
        header('Location: bookingsAdmin' . (preg_match('/^[a-z=&]*$/', $q) && $q !== '' ? '?' . $q : ''));
        exit;
    }
}
