<?php
/**
 * This class tests the core resources
 *
 * Copyright (C) 2011 by iRail vzw/asbl 
 * Author: Jens Segers
 * License: AGPLv3
 */

include_once(dirname(__FILE__)."/simpletest/autorun.php");
include_once(dirname(__FILE__)."/TDTUnitTest.class.php");
include_once(dirname(__FILE__)."/../classes/REST.class.php");

class APITestCore extends TDTUnitTest{

    function testGetTDTInfo(){
        $url = Config::$HOSTNAME . "TDTInfo/"; 
        $request = new REST($url, array(), "GET");
        $request->execute();
        
        $this->assertEqual($request->http_code, 572);
        if($this->http_code != 572 && $request->result)
            echo $request->result;
    }
    
    function testGetResources(){
        $url = Config::$HOSTNAME . "TDTInfo/Resources.about"; 
        $request = new REST($url, array(), "GET");
        $request->execute();
        
        $this->assertEqual($request->http_code, 200);
        if($this->http_code != 200 && $request->result)
            echo $request->result;
    }
    
    function testGetPackages() {
        $url = Config::$HOSTNAME . "TDTInfo/Packages.about"; 
        $request = new REST($url, array(), "GET");
        $request->execute();
        
        $this->assertEqual($request->http_code, 200);
        if($this->http_code != 200 && $request->result)
            echo $request->result;
    }
    
    function testGetQueries() {
        $url = Config::$HOSTNAME . "TDTInfo/Queries/TDTInfo.about"; 
        $request = new REST($url, array(), "GET", Config::$API_USER, Config::$API_PASSWD);
        $request->execute();
        
        $this->assertEqual($request->http_code, 200);
        if($this->http_code != 200 && $request->result)
            echo $request->result;
    }

}


?>