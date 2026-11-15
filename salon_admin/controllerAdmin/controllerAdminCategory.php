<?php
class controllerAdminCategory {

    public static function CategoryList($result = null, $edit = null) {
        $arr = modelAdminCategory::getCategoryList();
        $icons = modelAdminCategory::ICONS;
        include_once 'viewAdmin/categoryList.php';
    }

    public static function CategoryAdd() {
        return self::CategoryList(modelAdminCategory::getCategoryAdd());
    }

    public static function CategoryEditForm($id) {
        $edit = modelAdminCategory::getCategoryDetail($id);
        if (!$edit) {
            return controllerAdmin::error404();
        }
        return self::CategoryList(null, $edit);
    }

    public static function CategoryEditResult($id) {
        return self::CategoryList(modelAdminCategory::getCategoryEdit($id));
    }

    public static function CategoryDelete($id) {
        return self::CategoryList(modelAdminCategory::getCategoryDelete($id));
    }
}
