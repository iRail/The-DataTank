<?php
/* Copyright (C) 2011 by iRail vzw/asbl
 *
 * Author: Pieter Colpaert <pieter aŧ iRail.be>
 * License: AGPLv3
 *
 * These classes extend the Exception class to make our own well-documented Exception-system
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

     public static $error = 404;

     public function getErrorCode(){
	  return MethodOrModuleNotFoundTDTException::$error;
     }
}

/**
 *
 */
class NotAMethodTDTException extends AbstractTDTException{
     public static function getDoc(){
	  return "This function is not a method";
     }

     public function __construct() {
	  parent::__construct("Not a method");
     }

     public static $error = 410;

     public function getErrorCode(){
	  return NotAMethodTDTException::$error;
     }
}

class FormatNotAllowedTDTException extends AbstractTDTException{

     public static function getDoc(){
	  return "When a certain format is given with the request and it is not allowed by the method. This exception is thrown, and the allowed formats are show to the user.";
     }

     public function __construct($m, $method) {
	  $message = "Format not allowed: " . $m . ". Allowed formats are : <br> ";
	  foreach($method->allowedPrintMethods() as $format){
	       $message  = $message . " $format <br>";
	  }
	  
	  parent::__construct($message);
     }

     public static $error = 405;

     public function getErrorCode(){
	  return FormatNotAllowedTDTException::$error;
     }
}

class ParameterTDTException extends AbstractTDTException{
     public static function getDoc(){
	  return "This exception is thrown when a parameter is incorrect. The constructor needs a parameter";
     }

     public static $error = 401;

     public function getErrorCode(){
	  return ParameterTDTException::$error;
     }

     public function __construct($parameter){
	  parent::__construct("Parameter not found or incorrect: " . $parameter);
     }
}

class ParameterDoesntExistTDTException extends AbstractTDTException{
     public static function getDoc(){
	  return "This exception is thrown when a parameter does not exist. The constructor needs a parameter";
     }

     public static $error = 402;

     public function getErrorCode(){
	  return ParameterDoesntExistTDTException::$error;
     }

     public function __construct($parameter){
	  parent::__construct("Parameter does not exist: " . $parameter);
     }
}

/**
 * These are HTTP 500 errors: internal server errors
 */
class CouldNotGetDataTDTException extends AbstractTDTException{
     public static function getDoc(){
	  return "This exception is thrown when the data could not be resolved.";
     }

     public static $error = 501;

     public function getErrorCode(){
	  return CouldNotGetDataTDTException::$error;
     }

     public function __construct($datasourcename){
	  parent::__construct("This could not be resolved: " . $datasourcename);
     }
}

class InternalServerTDTException extends AbstractTDTException{
     public static function getDoc(){
	  return "This exception is thrown when a fatal error occurs. This due unexpected errors i.e. a file that couldn't be opened."
	          . "For further information check /var/log/apache2/error.log";
     }

     public static $error = 502;
     public function getErrorCode(){
	  return InternalServerTDTException::$error;
     }

     public function __construct($message){
	  parent::__construct($message);
     }
}
class NoPrinterTDTException extends AbstractTDTException{
     public static function getDoc(){
	  return "No printer is available or something went wrong in the Formatter class";
     }

     public static $error = 504;

     public function getErrorCode(){
	  return NoPrinterTDTException::$error;
     }

     public function __construct(){
	  parent::__construct("Formatter error. Check the value of your format parameter");
     }
}

class PrinterTDTException extends AbstractTDTException{
     public static function getDoc(){
	  return "Printer is available but a problem occured while trying to print the element.";
     }

     public static $error = 505;

     public function getErrorCode(){
	  return PrinterTDTException::$error;
     }

     public function __construct($message){
	  parent::__construct("Do you think the Printer cares? No it doens't cares, it just throw an exception. It's so bad-ass: " . $message);
     }
}


?>