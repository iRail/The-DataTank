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

    function testCreateMapping() {
        $url = Config::$HOSTNAME . Config::$SUBDIR . "TDTInfo/Ontology/Vienna/geo/Array/1615/stdClass/long";

        $postvar = array(
            'update_type' => 'ontology',
            'method' => 'create'
        );


        $request = new REST($url, $postvar, "POST");

        $request->execute();

        $this->assertEqual($request->http_code, 200);
        if ($request->http_code != 200 && $request->result)
            $this->debug($request->result);
        else {
            echo $request->result;
        }
        
        $url = Config::$HOSTNAME . Config::$SUBDIR . "TDTInfo/Ontology/Vienna/geo/Array/1615/stdClass/lat";

        $postvar = array(
            'update_type' => 'ontology',
            'method' => 'create'
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

    function testAddMapping() {
        $url = Config::$HOSTNAME . Config::$SUBDIR . "TDTInfo/Ontology/Vienna/geo/Array/1615/stdClass/lat";

        $postvar = array(
            'update_type' => 'ontology',
            'method' => 'map',
            'value' => 'http://www.w3.org/2003/01/geo/wgs84_pos#lat'
        );


        $request = new REST($url, $postvar, "POST");

        $request->execute();

        $this->assertEqual($request->http_code, 200);
        if ($request->http_code != 200 && $request->result)
            $this->debug($request->result);
        else {
            echo $request->result;
        }

        $url = Config::$HOSTNAME . Config::$SUBDIR . "TDTInfo/Ontology/Vienna/geo/Array/1615/stdClass/lon";

        $postvar = array(
            'update_type' => 'ontology',
            'method' => 'map',
            'value' => 'http://www.w3.org/2003/01/geo/wgs84_pos#lon'
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

}

?>