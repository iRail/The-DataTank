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

class REST {

    private $ch;
    
    public $url;
    public $http_code;
    public $result;
    public $curl_info;
    public $user;
    public $passwd;
    public $type = "GET";

    public function __construct($url,$data=array(), $type="GET", $user="", $passwd=""){
        if($type)
            $this->type = $type;
        if($user)
            $this->user = $user;
        else
            $this->user = Config::$API_USER;
        if($passwd)
            $this->passwd = $passwd;
        else
            $this->passwd = Config::$API_PASSWD;
        if($type)
            $this->type = $type;
        
        $this->ch = curl_init();
        $this->url = $url;
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);  
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $this->type);
        curl_setopt($this->ch, CURLOPT_URL, $this->url); 
        curl_setopt($this->ch, CURLOPT_USERPWD, "$this->user:$this->passwd");
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array("Accept: text/html, application/json")); 
    }    

    public function execute(){
        $this->result = curl_exec($this->ch);
        $this->curl_info = curl_getinfo($this->ch);
        $this->http_code = $this->curl_info["http_code"];
        curl_close($this->ch);
        
        return $this->http_code;
    }

    /*
     * This function checks whether or not the resource already exists
     * Doing so it will provide us with the correct expected http response code
     * 200 for non existing resources
     * 400 for existing resources
     */
    public function expectedHttpResponse($format){
        // the url should be an entity, so let's rtrim the url and put .about to it.
        $this->url = rtrim($this->url,"/");
        $this->url = $this->url.".".$format;

        if(@fopen($this->url, 'r')){
            return 400;
        }
        return 200;
    }
    
}
?>