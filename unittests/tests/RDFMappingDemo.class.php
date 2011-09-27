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
        if($request->http_code != 200 && $request->result)
            $this->debug($request->result);
        
        
        
        $postvar = array(
            'update_type' => 'rdf_mapping',
            'rdf_mapping_method' => 'update',
            'rdf_mapping_bash' => '*',
            'rdf_mapping_class' => 'lode:Event',
            'rdf_mapping_nmsp' => 'http://linkedevents.org/ontology/'
        );
        
        $url = Config::$HOSTNAME . Config::$SUBDIR . "GentseFeesten/Events/0/event/0/titel"; 
                
        $request = new REST($url, $postvar, "POST");
         
        $request->execute();
        
        $this->assertEqual($request->http_code, 200);
        if($request->http_code != 200 && $request->result)
            $this->debug($request->result);
    }
   
}

?>
