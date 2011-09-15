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
    public static function logRequest($package, $resource,$requiredparams,$subresources) {
	//an instance of printerfactory so we can check the format
	$ff = FormatterFactory::getInstance();

        $request = R::dispense('requests');
        $request->time = time();
        if(isset($_SERVER['HTTP_USER_AGENT'])){    
            $request->user_agent = $_SERVER['HTTP_USER_AGENT'];
        }
        $request->ip = $_SERVER['REMOTE_ADDR'];
        $request->url_request = TDT::getPageUrl();
        $request->package = $package;
        $request->resource = $resource;
        $request->format = $ff->getFormat();
        $request->subresources = implode(";",$subresources); // DEPRECATED !!!!!!!
        $request->requiredparameter = implode(";",$requiredparams);
        $request->allparameters = implode(";",$subresources);
        $result = R::store($request);        
    }
}
?>
