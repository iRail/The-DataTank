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

class APITestInstalled extends TDTUnitTest{

    // rename this to the package and resource you want to test!
    private $package = "GentseFeesten";
    private $resource = "Events";
    
    function testGetPackage(){
        $url = Config::$HOSTNAME . $this->package . "/"; 
        $request = new REST($url, array(), "GET");
        $request->execute();
        
        $this->assertEqual($request->http_code, 572);
        if($request->http_code != 572 && $request->result)
            $this->debug($request->result);
    }
    
    function testGetResource(){
        $url = Config::$HOSTNAME . $this->package . "/" . $this->resource . ".about"; 
        $request = new REST($url, array(), "GET");
        $request->execute();
        
        if($request->http_code == 200) {
            $this->pass();
        }
        elseif($request->http_code == 454) {
            $this->pass($request->result);
            if($request->result)
                $this->debug($request->result);
        }
        else {
            $this->fail();
            if($request->result)
                $this->debug($request->result);
        }
    }

}


?>