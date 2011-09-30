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
        
        $this->addFile(dirname(__FILE__) . "/APITestCore.class.php");
        $this->addFile(dirname(__FILE__) . "/APITestInstalled.class.php");
        $this->addFile(dirname(__FILE__) . "/APITestGenericCSV.class.php");
        $this->addFile(dirname(__FILE__) . "/APITestGenericRemoteCSV.class.php");
        
        if(isset(Config::$PHPEXCEL_IOFACTORY_PATH))
        $this->addFile(dirname(__FILE__) . "/APITestGenericXLS.class.php");
        
        $this->addFile(dirname(__FILE__) . "/APITestGenericHTMLTable.class.php");
        $this->addFile(dirname(__FILE__) . "/APITestGenericOGDWienJSON.class.php");        
        $this->addFile(dirname(__FILE__) . "/APITestGenericDB.class.php");
        $this->addFile(dirname(__FILE__) . "/APITestRemote.class.php");
    }

}

?>