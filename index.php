<?php
session_set_cookie_params(array('path' => '/', 'httponly' => true, 'samesite' => 'Lax'));
session_start();
include_once 'inc/Database.php';
include_once 'inc/Lang.php';
Lang::init();

require 'model/Category.php';
require 'model/Service.php';
require 'model/Master.php';
require 'model/Review.php';
require 'model/Booking.php';
require 'model/Register.php';
require 'model/Auth.php';

include_once 'view/Icons.php';
include_once 'view/ViewService.php';
include_once 'view/ViewReview.php';

include_once 'controller/Controller.php';
include_once 'route/routing.php';

echo $response;
