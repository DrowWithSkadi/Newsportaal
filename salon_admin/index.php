<?php
session_set_cookie_params(array('path' => '/', 'httponly' => true, 'samesite' => 'Lax'));
session_start();
require_once '../inc/Database.php';
require_once '../inc/Lang.php'; // abifunktsioon e() ja Lang::field()
require_once '../model/Auth.php'; // sisselogimine ja lukustus

include_once 'modelAdmin/modelAdmin.php';
include_once 'modelAdmin/modelAdminService.php';
include_once 'modelAdmin/modelAdminCategory.php';
include_once 'modelAdmin/modelAdminBooking.php';

include_once 'controllerAdmin/controllerAdmin.php';
include_once 'controllerAdmin/controllerAdminService.php';
include_once 'controllerAdmin/controllerAdminCategory.php';
include_once 'controllerAdmin/controllerAdminBooking.php';

include 'routeAdmin/routingAdmin.php';

echo $response;
