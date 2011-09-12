<?php
/**
 *
 * This class contains testcode to test our API back-end of the DataTank
 *
 * Copyright (C) 2011 by iRail vzw/asbl 
 * Author: Jan Vansteenlandt <jan at iRail.be>
 * License: AGPLv3
 */

include_once(dirname(__FILE__)."/simpletest/autorun.php");
include_once(dirname(__FILE__)."/../classes/PutAction.class.php");
include_once(dirname(__FILE__)."/../classes/DeleteAction.class.php");
include_once(dirname(__FILE__)."/../../Config.class.php");

/*
 * Note: fill in your credentials for authentication
 * All API transactions are done with curl ! (install if if you don't have it yet)
 */
class APITest extends UnitTestCase{

    private $user; 
    private $pwd;  
    
    public function __construct(){
        $this->user = Config::$API_USER;
        $this->pwd  = Config::$API_PASSWD;
        
    }

    /*
     * PUT functionality of the API
     */

    function testPutAction(){

        /*
         * PUT a csv-resource, this requires a test.csv file in your /var/www
         * you can always change the file URI ofcourse to w/e valid csv you might have
         */
        $url = Config::$HOSTNAME . "csvpackage/person/";
        $data = array( "resource_type" => "generic",
                       "printmethods"  => "json;xml;jsonp",
                       "generic_type"  => "CSV",
                       "documentation" => "this is some documentation.",
                       "uri"           => Config::$INSTALLDIR."/unittests/temp/person.csv",
                       "columns"       => "name;age;city",
                       "PK"            => "name"
        );
        
        $putAction = new PutAction($url,$data,$this->user,$this->pwd);

        // get the expected http response code in order to know that the tested functionality is A-ok !
        $expected_http_response = $putAction->expectedHttpResponse("json");

        // execute the action
        $result = $putAction->execute();

        // compare the actual result with the expected result
        $this->assertTrue($this->checkHttpResponseCode($result,$expected_http_response));
    }

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

    private function checkHttpResponseCode($result,$expected_http_response){
        return $result >= $expected_http_response && $result < $expected_http_response+100;
    }


    /*
     * POST functionality of the API
     */
    function testPostAction(){
        
    }
    

}


?>