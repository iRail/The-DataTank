<?php

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

?>

