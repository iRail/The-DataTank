<?php
/**
 * This class tests the generic XLS resource
 *
 * @package The-Datatank/model/resources/strategies
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Lieven Janssen
 */

include_once(dirname(__FILE__)."/simpletest/autorun.php");
include_once(dirname(__FILE__)."/TDTUnitTest.class.php");
include_once(dirname(__FILE__)."/../classes/REST.class.php");

class APITestGenericXLS extends TDTUnitTest{

    private $location = "/../temp/person.xlsx";
    private $sheet = "person";
    private $install_as = "xlspackage/person/";
    private $generic_type = "XLS";
    private $printmethods = "html;json;xml;jsonp";
    private $columns = "name;age;city";
    private $PK = "name";
    
    function testPutXLS(){
        
        $url = Config::$HOSTNAME . Config::$SUBDIR . $this->install_as;
        $data = array( "resource_type" => "generic",
                       "printmethods"  => $this->printmethods,
                       "generic_type"  => $this->generic_type,
                       "documentation" => "this is some documentation.",
                       "url"           => dirname(__FILE__).$this->location,
                       "sheet"         => $this->sheet,
                       "columns"       => $this->columns,
                       "PK"            => $this->PK
        );
        
        $request = new REST($url, $data, "PUT");
        $request->execute();
        
        $this->assertEqual($request->http_code, 200);
        if($request->result)
            $this->debug($request->result);
    }
    
    function testGetXLS() {
        $url = Config::$HOSTNAME . Config::$SUBDIR . $this->install_as; 
        $request = new REST($url, array(), "GET");
        $request->execute();
        
        $this->assertEqual($request->http_code, 200);
        if($request->http_code != 200 && $request->result)
            $this->debug($request->result);
    }

    function testDeleteXLS() {
        $url = Config::$HOSTNAME . Config::$SUBDIR . $this->install_as;
        $request = new REST($url, array(), "DELETE");
        $request->execute();
        
        $this->assertEqual($request->http_code, 200);
        if($request->result)
            $this->debug($request->result);
    }
}

?>