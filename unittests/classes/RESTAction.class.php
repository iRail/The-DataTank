<?php
/**
 *
 * This class contains the skeleton for a PutAction.
 * These classes are meant to be tested in the testPutAction() section of the API unittest.
 *
 * Copyright (C) 2011 by iRail vzw/asbl 
 * Author: Jan Vansteenlandt <jan at iRail.be>
 * License: AGPLv3
 */

abstract class RESTAction{

    protected $ch;
    protected $url;

    public function __construct($url,$data,$user,$passwd,$type){
        $this->ch = curl_init();
        $this->url = $url;
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);  
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $type);
        curl_setopt($this->ch, CURLOPT_URL, $this->url); 
        curl_setopt($this->ch, CURLOPT_USERPWD, "$user:$passwd");
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }    

    public function execute(){
        curl_exec($this->ch);
        // get the response code
        $curl_info = curl_getinfo($this->ch);
        $http_code = $curl_info["http_code"];
        curl_close($this->ch);
        return $http_code;
    }

    /*
     * This function checks whether or not the resource already exists
     * Doing so it will provide us with the correct expected http response code
     * 200 for non existing resources
     * 400 for existing resources
     */
    public abstract function expectedHttpResponse($format);
    
}
?>