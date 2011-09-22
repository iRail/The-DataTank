<?php
/**
 * This class tests the core resources
 *
 * Copyright (C) 2011 by iRail vzw/asbl 
 * Author: Jens Segers
 * License: AGPLv3
 */

include_once(dirname(__FILE__)."/simpletest/autorun.php");
include_once(dirname(__FILE__)."/TDTUnitTest.class.php");
include_once(dirname(__FILE__)."/../classes/REST.class.php");

class APITestGenericRemoteCSV extends TDTUnitTest{

    private $location = "http://www.wien.gv.at/statistik/ogd/vie-district-pop-foreignborn.csv";
    private $install_as = "vienna/population/";
    private $generic_type = "CSV";
    private $printmethods = "html;json;xml;jsonp";
    private $columns = "NUTS2;DISTRICT_CODE;NAME;SEX;VIE_POP_TOTAL;VIE_POP_AUT;VIE_POP_DEU;VIE_POP_POL;VIE_POP_ROU;VIE_POP_SVK;VIE_POP_HUN;VIE_POP_BGR;VIE_POP_CZE;VIE_POP_ EU_REST;VIE_POP_SCG;VIE_POP_TUR;VIE_POP_BIH;VIE_POP_HRV;VIE_POP_MKD;VIE_POP_RUS;VIE_POP_CHN;VIE_POP_OTHER;REF_DATE";
    
    function testPutRemoteCSV(){
        
        $url = Config::$HOSTNAME . Config::$SUBDIR . $this->install_as;
        $data = array( "resource_type" => "generic",
                       "printmethods"  => $this->printmethods,
                       "generic_type"  => $this->generic_type,
                       "documentation" => "this is some documentation.",
                       "uri"           => $this->location,
                       "columns"       => $this->columns
        );
        
        $request = new REST($url, $data, "PUT");
        $request->execute();
        
        $this->assertEqual($request->http_code, 200);
        if($request->result)
            $this->debug($request->result);
    }
    
    function testGetRemoteCSV() {
        $url = Config::$HOSTNAME . Config::$SUBDIR . $this->install_as; 
        $request = new REST($url, array(), "GET");
        $request->execute();
        
        $this->assertEqual($request->http_code, 200);
        if($request->http_code != 200 && $request->result)
            $this->debug($request->result);
    }

    function testDeleteRemoteCSV() {
        $url = Config::$HOSTNAME . Config::$SUBDIR . $this->install_as;
        $request = new REST($url, array(), "DELETE");
        $request->execute();
        
        $this->assertEqual($request->http_code, 200);
        if($request->result)
            $this->debug($request->result);
    }
}


?>