<?php

// View module
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

// Add module
class AdminAddModuleHandler {
    public function GET() {
        include_once ("templates/TheDataTank/header.php");
        $add_form = '
            <h2>Add Module</h2>
            <form name="Add Module" method="POST">
                name: <input type="text" name="name" /><br /><br />
                <input type="submit" value="Save" />
            </form>';
        echo $add_form;
        include_once ("templates/TheDataTank/footer.php");
    }

    public function POST() {
        R::setup(Config::$DB, Config::$DB_USER, Config::$DB_PASSWORD);
        $module = R::dispense('module');
        $module->module_name = $_POST['name'];
        $id = R::store($module);
        header('Location: ' . Config::$HOSTNAME . Config::$SUBDIR . 'admin/modules/');
    }
}

// Remove module
class AdminRemoveModuleHandler {
    public function GET($matches) {
        R::setup(Config::$DB, Config::$DB_USER, Config::$DB_PASSWORD);
        $module = R::load('module', $matches['module_id']);
        R::trash($module);
        header('Location: ' . Config::$HOSTNAME . Config::$SUBDIR . 'admin/modules/');
    }
}

?>
