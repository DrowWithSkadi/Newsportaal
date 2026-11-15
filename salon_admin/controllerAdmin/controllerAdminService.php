<?php
class controllerAdminService {

    public static function ServiceList() {
        $arr = modelAdminService::getServiceList();
        include_once 'viewAdmin/serviceList.php';
    }

    public static function ServiceAddForm() {
        $arr = modelAdminCategory::getCategoryList();
        $detail = array();
        $mode = 'add';
        include_once 'viewAdmin/serviceForm.php';
    }

    public static function ServiceAddResult() {
        list($test, $errors, $detail) = modelAdminService::getServiceAdd();
        $arr = modelAdminCategory::getCategoryList();
        $mode = 'add';
        include_once 'viewAdmin/serviceForm.php';
    }

    public static function ServiceEditForm($id) {
        $arr = modelAdminCategory::getCategoryList();
        $detail = modelAdminService::getServiceDetail($id);
        if (!$detail) {
            return controllerAdmin::error404();
        }
        $mode = 'edit';
        include_once 'viewAdmin/serviceForm.php';
    }

    public static function ServiceEditResult($id) {
        list($test, $errors, $detail) = modelAdminService::getServiceEdit($id);
        if ($test) {
            $detail = modelAdminService::getServiceDetail($id);
        }
        $arr = modelAdminCategory::getCategoryList();
        $mode = 'edit';
        include_once 'viewAdmin/serviceForm.php';
    }

    public static function ServiceDeleteForm($id) {
        $detail = modelAdminService::getServiceDetail($id);
        if (!$detail) {
            return controllerAdmin::error404();
        }
        include_once 'viewAdmin/serviceDeleteForm.php';
    }

    public static function ServiceDeleteResult($id) {
        $detail = modelAdminService::getServiceDetail($id);
        $test = modelAdminService::getServiceDelete($id);
        include_once 'viewAdmin/serviceDeleteForm.php';
    }
}
