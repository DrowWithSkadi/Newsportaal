<?php
// Marsruudi arvutamine aadressiribalt
$host = explode('?', $_SERVER['REQUEST_URI'])[0];
$num  = substr_count($host, '/');
$path = explode('/', $host)[$num];
$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';

if ($path == '' OR $path == 'index' OR $path == 'index.php') {
    $response = Controller::StartSite();
}
elseif ($path == 'services') {
    $response = Controller::AllServices();
}
elseif ($path == 'category' and isset($_GET['id'])) {
    $response = Controller::ServicesByCatID($_GET['id']);
}
elseif ($path == 'service' and isset($_GET['id'])) {
    $response = Controller::ServiceByID($_GET['id']);
}
elseif ($path == 'insertreview' and $isPost) {
    $response = Controller::InsertReview();
}
//----------------- broneerimine
elseif ($path == 'booking') {
    $response = Controller::BookingForm();
}
elseif ($path == 'bookingSave' and $isPost) {
    $response = Controller::BookingSave();
}
elseif ($path == 'bookingDone') {
    $response = Controller::BookingDone();
}
elseif ($path == 'slots' and isset($_GET['master'], $_GET['date'])) {
    $response = Controller::Slots($_GET['master'], $_GET['date']);
}
//----------------- muud lehed
elseif ($path == 'masters') {
    $response = Controller::Masters();
}
elseif ($path == 'contact') {
    $response = Controller::Contact();
}
elseif ($path == 'search') {
    $response = Controller::Search(isset($_GET['q']) ? $_GET['q'] : '');
}
//----------------- konto: sisse- ja väljalogimine
elseif ($path == 'login') {
    $response = $isPost ? Controller::loginAction() : Controller::loginForm();
}
elseif ($path == 'logout' and $isPost) {
    $response = Controller::logoutAction();
}
//----------------- kasutaja registreerimine
elseif ($path == 'registerForm') {
    $response = Controller::registerForm();
}
elseif ($path == 'registerAnswer' and $isPost) {
    $response = Controller::registerUser();
}
else {
    $response = Controller::error404();
}
