<?php
/**
 *
 * This class tests the API DeleteAction
 *
 * Copyright (C) 2011 by iRail vzw/asbl 
 * Author: Jens Segers
 * License: AGPLv3
 */

include_once(dirname(__FILE__)."/simpletest/autorun.php");
include_once(dirname(__FILE__)."/TDTUnitTest.class.php");
include_once(dirname(__FILE__)."/../classes/DeleteAction.class.php");

/*
 * Note: fill in your credentials for authentication
 * All API transactions are done with curl ! (install if if you don't have it yet)
 */
class APITestDeleteAction extends TDTUnitTest{

    /*
     * DELETE functionality of the API
     */
    function testDeleteAction(){
        $url = Config::$HOSTNAME . "csvpackage/person/"; 
        $data = array();
        $deleteAction = new DeleteAction($url,$data,$this->user,$this->pwd);
        
        $expected_http_response = $deleteAction->expectedHttpResponse("json");
        
        $result = $deleteAction->execute();
        
        $this->assertTrue($this->checkHttpResponseCode($result,$expected_http_response));
    }

}


?>