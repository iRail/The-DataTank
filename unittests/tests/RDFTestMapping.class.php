<?php
/**
 * This class tests the rdf mapping methods
 *
 * Copyright (C) 2011 by iRail vzw/asbl 
 * Author: Miel Vander Sande
 * License: AGPLv3
 */

include_once(dirname(__FILE__)."/simpletest/autorun.php");
include_once(dirname(__FILE__)."/TDTUnitTest.class.php");
include_once(dirname(__FILE__)."/../classes/REST.class.php");


class RDFTestMapping extends TDTUnitTest{
    
    public function __construct() {
        parent::__construct();
    }

    protected function debug($message) {
        var_dump($message);
    }
    
    function testAddMapping(){
        $url = Config::$HOSTNAME . Config::$SUBDIR . "TDTInfo/Resources/TDTInfo/Resources/doc"; 
        
        $postvar = array(
            'update_type' => 'rdf_mapping',
            'rdf_mapping_method' => 'update',
            'rdf_mapping_class' => 'documentation',
            'rdf_mapping_nmsp' => 'http://onto.cs.yale.edu:8080/ontologies/wsdl-ont.daml#'
        );
        
                
        $request = new REST($url, $postvar, "POST");
         
        $request->execute();
        
        $this->assertEqual($request->http_code, 200);
        if($request->http_code != 200 && $request->result)
            $this->debug($request->result);
    }
    
    function testDeleteMapping(){
        $url = Config::$HOSTNAME . Config::$SUBDIR . "TDTInfo/Resources/TDTInfo/Resources/doc"; 
        
        $postvar = array(
            'update_type' => 'rdf_mapping',
            'rdf_mapping_method' => 'delete',
        );
        
                
        $request = new REST($url, $postvar, "POST");
         
        $request->execute();
        
        $this->assertEqual($request->http_code, 200);
        if($request->http_code != 200 && $request->result)
            $this->debug($request->result);
    }
    
    function testEqualsMapping(){
        $url = Config::$HOSTNAME . Config::$SUBDIR . "TDTInfo/Resources/TDTInfo/Queries/doc"; 
        $eq_url = Config::$HOSTNAME . Config::$SUBDIR . "TDTInfo/Resources/TDTInfo/Resources/doc";
        
        $postvar = array(
            'update_type' => 'rdf_mapping',
            'rdf_mapping_method' => 'equals',
            'rdf_mapping_class' => $eq_url,
        );
        
                
        $request = new REST($url, $postvar, "POST");
         
        $request->execute();
        
        $this->assertEqual($request->http_code, 200);
        if($request->http_code != 200 && $request->result)
            $this->debug($request->result);
    }

    
}

?>
