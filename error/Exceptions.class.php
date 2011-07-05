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
abstract class AbstractTDTException extends Exception{
     public static function getDoc(){
	  return "No documentation given :(";
     }
     abstract public function getErrorCode();

     public function __construct($message) {
	  //Needs to be overridden - getErrorCode will return a HTTP-like errorcode according to REST specs
	  $code = $this->getErrorCode();
	  parent::__construct($message, $code);
     }
}

/**
 * These are HTTP 400 errors: Parameter or Methods not found
 */
class MethodOrModuleNotFoundTDTException extends AbstractTDTException{
     public static function getDoc(){
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
     public static function getDoc(){
	  return "This exception is thrown when a parameter is incorrect. The constructor needs a parameter";
     }

     public function getErrorCode(){
	  return 401;
     }

     public function __construct($parameter){
	  parent::__construct("Parameter not found or incorrect: " . $parameter);
     }
}

/**
 * These are HTTP 500 errors: internal server errors
 */
class CouldNotGetDataTDTException extends AbstractTDTException{
     public static function getDoc(){
	  return "This exception is thrown when the data could not be resolved.";
     }

     public function getErrorCode(){
	  return 501;
     }

     public function __construct($datasourcename){
	  parent::__construct("This could not be resolved: " . $datasourcename);
     }
}
?>