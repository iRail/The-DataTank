<?php
/** Copyright (C) 2011 by iRail vzw/asbl
 *
 * These classes extend the Exception class to make our own well-documented Exception-system
 *
 * Author: Pieter Colpaert <pieter aŧ iRail.be>
 * License: AGPLv3
 */

/**
 * The abstract function of TDT Exception
 */
class AbstractTDTException extends Exception{
     abstract public function getDoc();
     abstract public function getErrorCode();

     public function __construct($message) {
	  //Needs to be overridden - getErrorCode will return a HTTP-like errorcode according to REST specs
	  $code = $this->getErrorCode();
	  parent::__construct($message, $code);
     }
}

/**
 * These are HTTP 400 errors: Parameter or Methods not found
 *
 */
class MethodOrModuleNotFoundTDTException extends AbstractTDTException{
     public function getDoc(){
	  return "When a method or module is not found this Exception is thrown. The constructor expects the name of the module or the name of the method. This is a 404 error: not found";
     }

     public function __construct($m) {
	  parent::__construct("Method or module not found: " . $m);
     }

     public function getErrorCode(){
	  return 404;
     }
}

class ParameterTDTException extends AbstractTDTException{
     public function getDoc(){
	  return "This exception is thrown when a parameter is incorrect. The constructor needs a parameter on which it blocked and a message why it blocked: Parameter not found or Parameter incorrect";
     }

     public function getErrorCode(){
	  return 401;
     }

     public function __construct($parameter, $message){
	  parrent::__construct("Parameter not found or incorrect: " . $parameter);
     }
}

/**
 * These are HTTP 500 errors: internal server errors
 */ 
class CouldNotGetDataTDTException extends AbstractTDTException{
     public function getDoc(){
	  return "This exception is thrown when the data could not be resolved.";
     }

     public function getErrorCode(){
	  return 501;
     }

     public function __construct($datasourcename){
	  parrent::__construct("This could not be connected: " . $datasourcename);
     }
}
?>