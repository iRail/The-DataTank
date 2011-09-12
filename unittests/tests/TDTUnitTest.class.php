<?php
/**
 *
 * This class is used for writing test cases
 *
 * Copyright (C) 2011 by iRail vzw/asbl 
 * Author: Jens Segers
 * License: AGPLv3
 */

include_once(dirname(__FILE__)."/../../Config.class.php");

/*
 * Note: fill in your credentials for authentication
 * All API transactions are done with curl ! (install if if you don't have it yet)
 */
class TDTUnitTest extends UnitTestCase{

    protected $user; 
    protected $pwd;
    
    public function __construct(){
        $this->user = Config::$API_USER;
        $this->pwd  = Config::$API_PASSWD;
    }

    protected function checkHttpResponseCode($result,$expected_http_response){
        return $result >= $expected_http_response && $result < $expected_http_response+100;
    }

}

?>