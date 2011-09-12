<?php
/**
 *
 * This class contains testcode to test our API back-end of the DataTank (PUT)
 *
 * Copyright (C) 2011 by iRail vzw/asbl 
 * Author: Jan Vansteenlandt <jan at iRail.be>
 * License: AGPLv3
 */

include_once("RESTAction.class.php");

class PutAction extends RESTAction{

    public function __construct($url,$data,$user,$passwd){
        parent::__construct($url,$data,$user,$passwd,"PUT");
    }
    
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