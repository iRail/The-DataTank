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

    // the location of the csv resource
    private $location = "/../temp/person.csv";
    
    function testPutCSV(){
        
        $url = Config::$HOSTNAME . "csvpackage/person/";
        $data = array( "resource_type" => "generic",
                       "printmethods"  => "json;xml;jsonp",
                       "generic_type"  => "CSV",
                       "documentation" => "this is some documentation.",
                       "uri"           => dirname(__FILE__).$this->location,
                       "columns"       => "name;age;city",
                       "PK"            => "name"
        );
        
        $request = new REST($url, array(), "POST");
        $request->execute();
        
        echo $request->result.'<br><br>';
        print_r($request->curl_info);
    }
    
}


?>