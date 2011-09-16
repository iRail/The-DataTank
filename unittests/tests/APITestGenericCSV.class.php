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

class APITestGenericCSV extends TDTUnitTest{

    private $location = "/../temp/person.csv";
    private $install_as = "csvpackage/person/";
    private $generic_type = "CSV";
    private $printmethods = "json;xml;jsonp";
    private $columns = "name;age;city";
    private $PK = "name";
    
    function testPutCSV(){
        
        $url = Config::$HOSTNAME . Config::$SUBDIR . $this->install_as;
        $data = array( "resource_type" => "generic",
                       "printmethods"  => $this->printmethods,
                       "generic_type"  => $this->generic_type,
                       "documentation" => "this is some documentation.",
                       "uri"           => dirname(__FILE__).$this->location,
                       "columns"       => $this->columns,
                       "PK"            => $this->PK
        );
        
        $request = new REST($url, $data, "PUT");
        $request->execute();
        
        $this->assertEqual($request->http_code, 200);
        if($request->result)
            $this->debug($request->result);
    }
    
    function testGetCSV() {
        $url = Config::$HOSTNAME . Config::$SUBDIR . $this->install_as; 
        $request = new REST($url, array(), "GET");
        $request->execute();
        
        $this->assertEqual($request->http_code, 200);
        if($request->http_code != 200 && $request->result)
            $this->debug($request->result);
    }
    
    function testDeleteCSV() {
        $url = Config::$HOSTNAME . Config::$SUBDIR . $this->install_as;
        $request = new REST($url, array(), "DELETE");
        $request->execute();
        
        $this->assertEqual($request->http_code, 200);
        if($request->result)
            $this->debug($request->result);
    }
    
}


?>