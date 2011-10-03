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

class APITestGenericOGDWienJSON extends TDTUnitTest{

    //private $location = "http://www.nieuws.be/";
    //private $xpath = "//table[@id='htmlGrid_a119d7e2-e979-436a-8cad-765d37fc0fdd']";
    private $location = "http://data.wien.gv.at/daten/wfs?service=WFS&request=GetFeature&version=1.1.0&typeName=ogdwien:BUECHEREIOGD&srsName=EPSG:4326&outputFormat=json";
    private $install_as = "vienna/libraries/";
    private $generic_type = "OGDWienJSON";
    private $printmethods = "html;json;xml;jsonp";
    //private $columns = "Datum;Titel;Bron";
    //private $PK = "Titel";
    private $columns = "";
    //private $PK = "name";
    
    function testPutOGDWienJSON(){
        
        $url = Config::$HOSTNAME . Config::$SUBDIR . $this->install_as;
        $data = array( "resource_type" => "generic",
                       "printmethods"  => $this->printmethods,
                       "generic_type"  => $this->generic_type,
                       "documentation" => "this is some documentation.",
                       "url"           => $this->location,
                       "columns"       => $this->columns,
        );
        
        $request = new REST($url, $data, "PUT");
        $request->execute();
        
        $this->assertEqual($request->http_code, 200);
        if($request->result)
            $this->debug($request->result);
    }

    function testGetOGDWienJSON() {
        $url = Config::$HOSTNAME . Config::$SUBDIR . $this->install_as; 
        $request = new REST($url, array(), "GET");
        $request->execute();
        
        $this->assertEqual($request->http_code, 200);
        if($request->http_code != 200 && $request->result)
            $this->debug($request->result);
    }

    function testDeleteOGDWienJSON() {
        $url = Config::$HOSTNAME . Config::$SUBDIR . $this->install_as;
        $request = new REST($url, array(), "DELETE");
        $request->execute();
        
        $this->assertEqual($request->http_code, 200);
        if($request->result)
            $this->debug($request->result);
    }
}

?>