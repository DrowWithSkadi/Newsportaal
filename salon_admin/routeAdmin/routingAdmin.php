<?php
$host = explode('?', $_SERVER['REQUEST_URI'])[0];
$num  = substr_count($host, '/');
$path = explode('/', $host)[$num];
$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// ------ Sisselogimine (avalik) -------------
if ($path == '' OR $path == 'index.php') {
    $response = controllerAdmin::formLoginSite();
}
elseif ($path == 'login') {
    $response = controllerAdmin::loginAction();
}
elseif ($path == 'logout') {
    $response = controllerAdmin::logoutAction();
}
// ------ Kõik järgnevad lehed ainult administraatorile -------------
elseif (!modelAdmin::isAdmin()) {
    $response = controllerAdmin::formLoginSite();
}
elseif ($path == 'dashboard') {
    $response = controllerAdmin::dashboard();
}
// ------ Teenused -------------
elseif ($path == 'servicesAdmin') {
    $response = controllerAdminService::ServiceList();
}
elseif ($path == 'serviceAdd') {
    $response = controllerAdminService::ServiceAddForm();
}
elseif ($path == 'serviceAddResult' and $isPost) {
    $response = controllerAdminService::ServiceAddResult();
}
elseif ($path == 'serviceEdit' and $id) {
    $response = controllerAdminService::ServiceEditForm($id);
}
elseif ($path == 'serviceEditResult' and $id and $isPost) {
    $response = controllerAdminService::ServiceEditResult($id);
}
elseif ($path == 'serviceDel' and $id) {
    $response = controllerAdminService::ServiceDeleteForm($id);
}
elseif ($path == 'serviceDelResult' and $id and $isPost) {
    $response = controllerAdminService::ServiceDeleteResult($id);
}
// ------ Kategooriad -------------
elseif ($path == 'categoriesAdmin') {
    $response = controllerAdminCategory::CategoryList();
}
elseif ($path == 'categoryAdd' and $isPost) {
    $response = controllerAdminCategory::CategoryAdd();
}
elseif ($path == 'categoryEdit' and $id) {
    $response = controllerAdminCategory::CategoryEditForm($id);
}
elseif ($path == 'categoryEditResult' and $id and $isPost) {
    $response = controllerAdminCategory::CategoryEditResult($id);
}
elseif ($path == 'categoryDel' and $id and $isPost) {
    $response = controllerAdminCategory::CategoryDelete($id);
}
// ------ Broneeringud -------------
elseif ($path == 'bookingsAdmin') {
    $response = controllerAdminBooking::BookingList();
}
elseif ($path == 'bookingStatus' and $isPost) {
    $response = controllerAdminBooking::BookingStatus();
}
elseif ($path == 'bookingDel' and $isPost) {
    $response = controllerAdminBooking::BookingDelete();
}
else {
    $response = controllerAdmin::error404();
}
