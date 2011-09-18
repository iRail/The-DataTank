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

class APITestGenericHTMLTable extends TDTUnitTest{

    //private $location = "http://www.nieuws.be/";
    //private $xpath = "//table[@id='htmlGrid_a119d7e2-e979-436a-8cad-765d37fc0fdd']";
    private $location = "/unittests/temp/person.html";
    private $xpath = "//table";
    private $install_as = "htmltablepackage/person/";
    private $generic_type = "HTMLTable";
    private $printmethods = "html;json;xml;jsonp";
    //private $columns = "Datum;Titel;Bron";
    //private $PK = "Titel";
    private $columns = "name;age;city";
    private $PK = "name";
    
    function testPutHTMLTable(){
        
        $url = Config::$HOSTNAME . Config::$SUBDIR . $this->install_as;
        $data = array( "resource_type" => "generic",
                       "printmethods"  => $this->printmethods,
                       "generic_type"  => $this->generic_type,
                       "documentation" => "this is some documentation.",
                       "uri"           => Config::$HOSTNAME . Config::$SUBDIR . $this->location,
                       "xpath"         => $this->xpath,
                       "columns"       => $this->columns,
                       "PK"            => $this->PK
        );
        
        $request = new REST($url, $data, "PUT");
        $request->execute();
        
        $this->assertEqual($request->http_code, 200);
        if($request->result)
            $this->debug($request->result);
    }
    
    function testGetHTMLTable() {
        $url = Config::$HOSTNAME . Config::$SUBDIR . $this->install_as; 
        $request = new REST($url, array(), "GET");
        $request->execute();
        
        $this->assertEqual($request->http_code, 200);
        if($request->http_code != 200 && $request->result)
            $this->debug($request->result);
    }

    function testDeleteHTMLTable() {
        $url = Config::$HOSTNAME . Config::$SUBDIR . $this->install_as;
        $request = new REST($url, array(), "DELETE");
        $request->execute();
        
        $this->assertEqual($request->http_code, 200);
        if($request->result)
            $this->debug($request->result);
    }
}

?>