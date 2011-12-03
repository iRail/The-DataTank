<?php
/**
 * This class tests the core resources
 *
 * @package The-DataTank/unittests/tests
 * @copyright (C) 2011 by iRail vzw/asbl 
 * @author: Jens Segers
 * License: AGPLv3
 */

include_once(dirname(__FILE__)."/simpletest/autorun.php");
include_once(dirname(__FILE__)."/TDTUnitTest.class.php");
include_once(dirname(__FILE__)."/../classes/REST.class.php");

class APITestCore extends TDTUnitTest{

    function testGetTDTInfo(){
        $url = Config::$HOSTNAME . Config::$SUBDIR . "TDTInfo/"; 
        $request = new REST($url, array(), "GET");
        $request->execute();
        
        $this->assertEqual($request->http_code, 572);
        if($request->http_code != 572 && $request->result)
            $this->debug($request->result);
    }
    
    function testGetResources(){
        $url = Config::$HOSTNAME . Config::$SUBDIR . "TDTInfo/Resources.about"; 
        $request = new REST($url, array(), "GET");
        $request->execute();
        
        $this->assertEqual($request->http_code, 200);
        if($request->http_code != 200 && $request->result)
            $this->debug($request->result);
    }
    
    function testGetPackages() {
        $url = Config::$HOSTNAME . Config::$SUBDIR . "TDTInfo/Packages.about"; 
        $request = new REST($url, array(), "GET");
        $request->execute();
        
        $this->assertEqual($request->http_code, 200);
        if($request->http_code != 200 && $request->result)
            $this->debug($request->result);
    }
    
    function testGetQueries() {
        $url = Config::$HOSTNAME . Config::$SUBDIR . "TDTInfo/Queries/TDTInfo.about"; 
        $request = new REST($url, array(), "GET", Config::$API_USER, Config::$API_PASSWD);
        $request->execute();
        
        $this->assertEqual($request->http_code, 200);
        if($request->http_code != 200 && $request->result)
            $this->debug($request->result);
    }

}


?>