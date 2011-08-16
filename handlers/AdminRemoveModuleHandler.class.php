<?php

class AdminRemoveModuleHandler {
    public function GET($matches) {
        R::setup(Config::$DB, Config::$DB_USER, Config::$DB_PASSWORD);
        $module = R::load('module', $matches['module_id']);
        R::trash($module);
        header('Location: ' . Config::$HOSTNAME . Config::$SUBDIR . 'admin/modules/');
    }
}

?>


