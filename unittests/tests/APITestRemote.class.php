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

class APITestRemote extends TDTUnitTest{
    
    private $install_as = "remotepackage/NewsFeed/";
    private $resource_type = "remote";
    private $package_name = "VerkeersCentrum";
    private $base_url = "http://datatank.demo.ibbt.be/";
    
    function testPutRemote(){
        
        $url = Config::$HOSTNAME . $this->install_as;
        $data = array( "resource_type" => "remote",
                       "package_name"  => $this->package_name,
                       "base_url" 	   => $this->base_url
        );
        
        $request = new REST($url, $data, "PUT");
        $request->execute();
        
        if($request->result)
            $this->debug($request->result);
            
        $this->assertEqual($request->http_code, 200);
    }
    
    function testGetRemote() {
        $url = Config::$HOSTNAME . $this->install_as; 
        $request = new REST($url, array(), "GET");
        $request->execute();
        
        $this->assertEqual($request->http_code, 200);
        if($request->http_code != 200 && $request->result)
            $this->debug($request->result);
    }
    
    /*function testDeleteRemote() {
        $url = Config::$HOSTNAME . $this->install_as;
        $request = new REST($url, array(), "DELETE");
        $request->execute();
        
        if($request->result)
            $this->debug($request->result);
        
        $this->assertEqual($request->http_code, 200);
    }*/
    
}


?>