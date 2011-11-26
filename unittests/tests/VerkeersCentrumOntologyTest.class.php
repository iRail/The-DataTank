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

class VerkeersCentrumOntologyTest extends TDTUnitTest {

    public function __construct() {
        parent::__construct();
    }

    protected function debug($message) {
        var_dump($message);
    }

    function testCreate() {
        $url = Config::$HOSTNAME . Config::$SUBDIR . "TDTInfo/Ontology/VerkeersCentrum/NewsFeed";

        $postvar = array(
            'ontology_file' => 'http://localhost/TDT/custom/packages/VerkeersCentrum/VerkeersCentrum.ttl'
        );


        $request = new REST($url, $postvar, "PUT");

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