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
    private $printmethods = "html;json;xml;jsonp";
    private $columns = "id;name";
    private $PK = "id";
   
    private $db_name = "test";
    private $db_table = "person";
    private $host = "localhost";
    private $db_type = "mysql";
    private $db_user = "root";
    private $db_password = "root";
    
    function testPutDB(){
        
        $url = Config::$HOSTNAME . Config::$SUBDIR . $this->install_as;
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
        
        $this->assertEqual($request->http_code, 200);
        if($request->result)
            $this->debug($request->result);
    }
    
    function testGetDB() {
        $url = Config::$HOSTNAME . Config::$SUBDIR . $this->install_as; 
        $request = new REST($url, array(), "GET");
        $request->execute();
        
        $this->assertEqual($request->http_code, 200);
        if($request->http_code != 200 && $request->result)
            $this->debug($request->result);
    }
    
    function testDeleteDB() {
        $url = Config::$HOSTNAME . Config::$SUBDIR . $this->install_as;
        $request = new REST($url, array(), "DELETE");
        $request->execute();
        
        $this->assertEqual($request->http_code, 200);
        if($request->result)
            $this->debug($request->result);
    }
    
}


?>