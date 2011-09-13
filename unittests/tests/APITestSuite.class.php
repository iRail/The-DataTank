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

class TestAPISuite extends TestSuite{

    function TestAPISuite() {
        $this->TestSuite('API Test Suite');
        
        $this->addFile(dirname(__FILE__) . "/APITestPutAction.class.php");
        $this->addFile(dirname(__FILE__) . "/APITestDeleteAction.class.php");
    }

}

?>