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

class RDFTestMapping extends TDTUnitTest {

    public function __construct() {
        parent::__construct();
    }

    protected function debug($message) {
        var_dump($message);
    }

    function testMapResources() {


        $url = Config::$HOSTNAME . Config::$SUBDIR . "GentseFeesten/Events/0/event/0";

        $postvar = array(
            'update_type' => 'rdf_mapping',
            'rdf_mapping_method' => 'update',
            'rdf_mapping_bash' => '*',
            'rdf_mapping_class' => 'lode:Event',
            'rdf_mapping_nmsp' => 'http://linkedevents.org/ontology/'
        );


        $request = new REST($url, $postvar, "POST");

        $request->execute();

        $this->assertEqual($request->http_code, 200);
        if ($request->http_code != 200 && $request->result)
            $this->debug($request->result);
        
        echo $request->result;
    }

    function testMapProperties() {
        
        $map_val = array(
            'latitude' => 'lat',
            'longitude' => 'lon'
        );
        $this->processRequest($map_val, 'wgs84', 'http://www.w3.org/2003/01/geo/wgs84_pos#');
        
        $map_val = array(
            'titel' => 'title',
            'datum' => 'date',
            'omschrijving' => 'description'
        );
        $this->processRequest($map_val, 'dc', 'http://purl.org/dc/elements/1.1/');
        
        $map_val = array(
            'begin' => 'start',
            'einde' => 'end'
        );
        $this->processRequest($map_val, 'tl', 'http://purl.org/NET/c4dm/timeline.owl#');
        
                    
        $map_val = array(
            'locatie' => 'eventLocation'
        );
        $this->processRequest($map_val, 'gen', 'http://www.cs.umd.edu/projects/plus/DAML/onts/base1.0.daml#');
        
    }

    private function processRequest($map_val,$prefix,$nmsp) {
        
        $postvar = array(
            'update_type' => 'rdf_mapping',
            'rdf_mapping_method' => 'update',
        );
        
        
        for ($i = 0; $i < 16; $i++) {
            foreach ($map_val as $key => $val) {
                $postvar['rdf_mapping_class'] = $prefix.':'.$val;
                $postvar['rdf_mapping_nmsp'] = $nmsp;
                
                $url = Config::$HOSTNAME . Config::$SUBDIR . "GentseFeesten/Events/0/event/" . $i . "/" . $key;
                $request = new REST($url, $postvar, "POST");
                $request->execute();

                $this->assertEqual($request->http_code, 200);
                if ($request->http_code != 200 && $request->result)
                    $this->debug($request->result);
            }
        }
    }

}

?>
