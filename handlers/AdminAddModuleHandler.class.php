<?php

class AdminAddModuleHandler {
    public function GET() {
        include_once ("templates/TheDataTank/header.php");
        $add_form = '
            <h2>Add Module</h2>
            <form name="Add Module" method="POST">
                name: <input type="text" name="name" /><br /><br />
                <input type="submit" value="Save" /><br />
            </form>
            <form name="Add Module" method="">
            <input type="hidden" name="/>
            </form>';
        echo $add_form;
        include_once ("templates/TheDataTank/footer.php");
    }

    public function POST() {

    }
}

?>

