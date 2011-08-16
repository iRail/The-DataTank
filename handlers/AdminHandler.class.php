<?php

class AdminHandler {
    public function GET() {
        include_once ("templates/TheDataTank/header.php");
        echo '<div><a href="/admin/modules/">Modules</a></div>';
        echo '<div><a href="/admin/resources/">Resources</a></div>';
        include_once ("templates/TheDataTank/footer.php");
    }
}

?>
