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

class APITestGenericDB extends TDTUnitTest{
    
    private $install_as = "dbpackage/person/";
    private $generic_type = "DB";
    private $printmethods = "json;xml;jsonp";
    private $columns = "name;age;city";
    private $PK = "name";
   
    private $db_name = "test";
    private $db_table = "person";
    private $host = "localhost";
    private $db_type = "My_SQL";
    private $db_user = "root";
    private $db_password = "root";
    
    function testPutCSV(){
        
        $url = Config::$HOSTNAME . $this->install_as;
        $data = array( "resource_type" => "generic",
                       "printmethods"  => $this->printmethods,
                       "generic_type"  => $this->generic_type,
                       "documentation" => "this is some documentation.",
                       "columns"       => $this->columns,
                       "PK"            => $this->PK,
                       "db_name"	   => $this->db_name,
                       "db_table"	   => $this->db_table,
                       "host"		   => $this->host,
                       "db_type"	   => $this->db_type,
                       "db_user"	   => $this->db_user,
                       "db_password"   => $this->db_password
        );
        
        $request = new REST($url, $data, "PUT");
        $request->execute();
        
        if($request->result)
            $this->debug($request->result);
            
        $this->assertEqual($request->http_code, 200);
    }
    
    function testGetDB() {
        $url = Config::$HOSTNAME . $this->install_as; 
        $request = new REST($url, array(), "GET");
        $request->execute();
        
        $this->assertEqual($request->http_code, 200);
        if($request->http_code != 200 && $request->result)
            $this->debug($request->result);
    }
    
    function testDeleteDB() {
        $url = Config::$HOSTNAME . $this->install_as;
        $request = new REST($url, array(), "DELETE");
        $request->execute();
        
        if($request->result)
            $this->debug($request->result);
        
        $this->assertEqual($request->http_code, 200);
    }
    
}


?>