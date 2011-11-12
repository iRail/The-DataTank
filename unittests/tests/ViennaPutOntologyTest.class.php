<?php

/**
 * This class tests the rdf mapping methods
 *
 * Copyright (C) 2011 by iRail vzw/asbl 
 * Author: Miel Vander Sande
 * License: AGPLv3
 */
include_once(dirname(__FILE__) . "/simpletest/autorun.php");
include_once(dirname(__FILE__) . "/TDTUnitTest.class.php");
include_once(dirname(__FILE__) . "/../classes/REST.class.php");

class ViennaOntologyTest extends TDTUnitTest {

    public function __construct() {
        parent::__construct();
    }

    protected function debug($message) {
        var_dump($message);
    }

    function testCreateOntology() {
        $url = Config::$HOSTNAME . Config::$SUBDIR . "TDTInfo/Ontology/Vienna/citybikes";

        $putvar = array(
            "type" => "class"
        );

        $request = new REST($url, $putvar, "PUT");

        $request->execute();

        $this->assertEqual($request->http_code, 200);
        if ($request->http_code != 200 && $request->result)
            $this->debug($request->result);
        else {
            echo $request->result;
        }
        
        $url = Config::$HOSTNAME . Config::$SUBDIR . "TDTInfo/Ontology/Vienna/citybikes/stdClass";

        $putvar = array(
            "type" => "class"
        );

        $request = new REST($url, $putvar, "PUT");

        $request->execute();

        $this->assertEqual($request->http_code, 200);
        if ($request->http_code != 200 && $request->result)
            $this->debug($request->result);
        else {
            echo $request->result;
        }
        
              
        $url = Config::$HOSTNAME . Config::$SUBDIR . "TDTInfo/Ontology/Vienna/citybikes/stdClass/long";

        $putvar = array(
            "type" => "property"
        );

        $request = new REST($url, $putvar, "PUT");

        $request->execute();

        $this->assertEqual($request->http_code, 200);
        if ($request->http_code != 200 && $request->result)
            $this->debug($request->result);
        else {
            echo $request->result;
        }
        
        $url = Config::$HOSTNAME . Config::$SUBDIR . "TDTInfo/Ontology/Vienna/citybikes/stdClass/lat";

        $putvar = array(
            "type" => "property"
        );

        $request = new REST($url, $putvar, "PUT");

        $request->execute();

        $this->assertEqual($request->http_code, 200);
        if ($request->http_code != 200 && $request->result)
            $this->debug($request->result);
        else {
            echo $request->result;
        }
    }
    
    function testMapOntology() {
       
        $url = Config::$HOSTNAME . Config::$SUBDIR . "TDTInfo/Ontology/Vienna/citybikes/stdClass/long";

        $postvar = array(
            'update_type' => 'ontology',
            'method' => 'map',
            'value' => 'lon',
            'namespace' => 'http://www.w3.org/2003/01/geo/wgs84_pos#',
            'prefix' => 'wgs84_pos'
            
        );

        $request = new REST($url, $postvar, "POST");

        $request->execute();

        $this->assertEqual($request->http_code, 200);
        if ($request->http_code != 200 && $request->result)
            $this->debug($request->result);
        else {
            echo $request->result;
        }
        
        $url = Config::$HOSTNAME . Config::$SUBDIR . "TDTInfo/Ontology/Vienna/citybikes/stdClass/lat";

        $postvar = array(
            'update_type' => 'ontology',
            'method' => 'map',
            'value' => 'lat',
            'namespace' => 'http://www.w3.org/2003/01/geo/wgs84_pos#',
            'prefix' => 'wgs84_pos'
        );

        $request = new REST($url, $postvar, "POST");

        $request->execute();

        $this->assertEqual($request->http_code, 200);
        if ($request->http_code != 200 && $request->result)
            $this->debug($request->result);
        else {
            echo $request->result;
        }
    }
    
    function testDeleteOntology() {
              
        $url = Config::$HOSTNAME . Config::$SUBDIR . "TDTInfo/Ontology/Vienna/citybikes/stdClass/id";

        $putvar = array(
  
        );

        $request = new REST($url, $putvar, "DELETE");

        $request->execute();

        $this->assertEqual($request->http_code, 200);
        if ($request->http_code != 200 && $request->result)
            $this->debug($request->result);
        else {
            echo $request->result;
        }
                     
        
    }
    
    

}

?>