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

    function testResourceMap() {
        $doc = array(
            'TDTInfo' => array(
                'Resources',
                'Queries',
                'Packages',
                'Exceptions',
                'Mapping'
            ),
            'GentseFeesten' => array(
                'Events'
            ),
            'VerkeersCentrum' => array(
                'NewsFeed'
            ),
            'Weather' => array(
                'Rainfall'
            )
        );

        foreach ($doc as $package => $value) {

            $postvar = array(
                'update_type' => 'rdf_mapping',
                'rdf_mapping_method' => 'update',
                'rdf_mapping_nmsp' => 'http://vocab.deri.ie/dcat#');

            foreach ($value as $resource) {

                $postvar['rdf_mapping_class'] = 'dcat:Dataset';



                $url = Config::$HOSTNAME . Config::$SUBDIR . "TDTInfo/Resources/" . $package . '/' . $resource;

                $request = new REST($url, $postvar, "POST");

                $request->execute();

                $this->assertEqual($request->http_code, 200);
                if ($request->http_code != 200 && $request->result)
                    $this->debug($request->result);



                $map_var = array(
                    'doc' => 'description',
                    'requiredparameters' => 'requiredparameters',
                    'parameters' => 'parameters',
                    'formats' => 'formats',
                    'modification_timestamp' => 'modified',
                    'creation_timestamp' => 'issued'
                );

                foreach ($map_var as $prop => $map) {
                    $postvar['rdf_mapping_class'] = 'dcat:' . $map;
                    $url = Config::$HOSTNAME . Config::$SUBDIR . "TDTInfo/Resources/" . $package . '/' . $resource . '/' . $prop;

                    $request = new REST($url, $postvar, "POST");

                    $request->execute();

                    $this->assertEqual($request->http_code, 200);
                    if ($request->http_code != 200 && $request->result)
                        $this->debug($request->result);
                }
            }
        }
    }

}

?>
