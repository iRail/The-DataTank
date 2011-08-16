<?php

class AdminModuleHandler {
    public function GET() {
        include_once ("templates/TheDataTank/header.php");
        R::setup(Config::$DB, Config::$DB_USER, Config::$DB_PASSWORD);
        $modules = '<h2>Modules</h2><ul>';
        foreach (R::find('module') as $module) {
            $modules .= '<li><a href="' . Config::$HOSTNAME . Config::$SUBDIR . 
                'admin/modules/' . $module->id . '/">' .
                htmlentities($module->module_name) .
                '</a> - <a href="' . Config::$HOSTNAME . Config::$SUBDIR .
                'admin/modules/' . $module->id . '/delete/">(del)</a></li>';
        }
        $modules .= '</ul>';
        echo $modules;
        echo '<a href="' . Config::$HOSTNAME . Config::$SUBDIR .
            'admin/modules/add/">+ Add Module</a>';
        //echo '<a id="add_module" href="/' . Config::$SUBDIR . '/"#">Add Module';
        include_once ("templates/TheDataTank/footer.php");
    }
}

?>
