<?php
/**
 *
 * This class contains a test suite to test our specific parts of the API back-end of the DataTank
 *
 * Copyright (C) 2011 by iRail vzw/asbl 
 * Author: Jens Segers
 * License: AGPLv3
 */

include_once(dirname(__FILE__)."/simpletest/autorun.php");
include_once(dirname(__FILE__)."/../../includes/rb.php");
include_once(dirname(__FILE__)."/../../Config.class.php");

R::setup(Config::$DB,Config::$DB_USER,Config::$DB_PASSWORD);

class FKTestSuite extends TestSuite{

    function FKTestSuite() {
        $this->TestSuite('FK Test Suite');
        
        $this->addFile(dirname(__FILE__) . "/FKTestResources.class.php");
        $this->addFile(dirname(__FILE__) . "/FKTestRelations.class.php");
    }

}

?>