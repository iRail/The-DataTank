<?php
/**
 * This file contains the RequestLogger.class.php
 * @package The-Datatank/requests
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@iRail.be>
 * @author Pieter Colpaert   <pieter@iRail.be>
 */ 
 
class RequestLogger{
    /**
     * This function implements the logging part of the RequestLogger functionality.
     */
    public static function logRequest() {
        R::setup(Config::$DB, Config::$DB_USER, Config::$DB_PASSWORD);
	//an instance of printerfactory so we can check the format
	$ff = FormatterFactory::getInstance();
        //an instance of RequestURI
        $URI = RequestURI::getInstance();
        $request = R::dispense('requests');
        $request->time = time();
        if(isset($_SERVER['HTTP_USER_AGENT'])){    
            $request->user_agent = $_SERVER['HTTP_USER_AGENT'];
        }
        $request->ip = $_SERVER['REMOTE_ADDR'];
        $request->url_request = $URI->getURI();
        $request->package = $URI->getPackage();
        $request->resource = $URI->getResource();
        $request->format = $ff->getFormat();
        $request->requiredparameter = implode(";",$URI->getFilters());
        if(!is_null($URI->getGET())){
            $request->allparameters = implode(";",$URI->getGET());
        }else{
            $request->allparameters = "";
        }
        
        $result = R::store($request);
    }
}
?>
