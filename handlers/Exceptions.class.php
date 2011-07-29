<?php

  /**
   * This file contains all the Exceptions specifically made for the DataTank.
   * @package The-Datatank/error
   * @copyright (C) 2011 by iRail vzw/asbl
   * @license AGPLv3
   * @author Jan Vansteenlandt <Jan@iRail.be>
   * @author Pieter Colpaert   <pieter@iRail.be>
   */


  /**
   * This is the abstract class of a TDT Exception
   */
abstract class AbstractTDTException extends Exception{
     /**
      * This function returns the documentation describing this exception.
      * @return The documentation of this exception.
      */
     public static function getDoc(){
	  return "No documentation given :(";
     }
     /**
      * This should return an errorcode which relates to the implemented exception class.
      */
     abstract public function getErrorCode();

     /**
      * Constructor.
      * @param string $message The message contains the error message.
      */
     public function __construct($message) {
	  //Needs to be overridden - getErrorCode will return a HTTP-like errorcode according to REST specs
	  $code = $this->getErrorCode();
	  parent::__construct($message, $code);
     }
  }

/**
 * These are HTTP 400 errors: Parameter or Methods not found
 */

/**
 * This class reprents an exception which is thrown when a given method or module is not valid.
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
 * This class reprents an exception which is thrown when the method given is not a valid method.
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

/**
 * This class reprents an exception which is thrown when a given format is not a valid one.
 */
class FormatNotAllowedTDTException extends AbstractTDTException{

     public static function getDoc(){
	  return "When a certain format is given with the request and it is not allowed by the method. This exception is thrown, and the allowed formats are show to the user.";
     }

     public function __construct($m, $format) { // format = array of allowed formats
	  $message = "Format not allowed: " . $m . ". Allowed formats are : <br> ";
	  foreach($format as $format){
	       $message  = $message . " $format <br>";
	  }
	  
	  parent::__construct($message);
     }

     public static $error = 405;

     public function getErrorCode(){
	  return FormatNotAllowedTDTException::$error;
     }
}

/**
 * This class reprents an exception which is thrown when a given parameter is not found or incorrect.
 */
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

/**
 * This class reprents an exception which is thrown when a given method or module is not valid.
 */
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

/**
 * This class reprents an exception which is thrown when a given method or module is not valid.
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

/**
 * This class reprents an exception which is thrown when a given method or module is not valid.
 */
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

/**
 * This class reprents an exception which is thrown when a given method or module is not valid.
 */
class RemoteServerTDTException extends AbstractTDTException{
     public static function getDoc(){
	  return "This error is thrown because a proxy call has gone wrong.".
	       "This probably due to remoteserver problem.";
     }

     public static $error = 502;
     public function getErrorCode(){
	  return InternalServerTDTException::$error;
     }

     public function __construct($message){
	  parent::__construct($message);
     }
}

/**
 * This class reprents an exception which is thrown when a given method or module is not valid.
 */
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

/**
 * This class reprents an exception which is thrown when a given method or module is not valid.
 */
class BadMethodCallTDTException extends AbstractTDTException{
     public static function getDoc(){
	  return "Bad method call";
     }

     public static $error = 450;

     public function getErrorCode(){
	  return self::$error;
     }

     public function __construct($message){
	  parent::__construct($message);
     }
}

/**
 * This class reprents an exception which is thrown when a given method or module is not valid.
 */
class NotFoundTDTException extends AbstractTDTException{
     public static function getDoc(){
	  return "Class not found!";
     }

     public static $error = 451;

     public function getErrorCode(){
	  return self::$error;
     }

     public function __construct($message){
	  parent::__construct($message);
     }
}
/**
 * This class reprents an exception which is thrown when a given method or module is not valid.
 */
class CouldNotParseUrlTDTException extends AbstractTDTException{
     public static function getDoc(){
	  return "When a wrong url is given or when the server cannot handle or url";
     }

     public static $error = 506;

     public function getErrorCode(){
	  return self::$error;
     }

     public function __construct($url){
	  parent::__construct("Could not parse url: " . $url);
     }
}

/**
 * This class reprents an exception which is thrown when a given method or module is not valid.
 */
class HttpOutTDTException extends AbstractTDTException{
     public static function getDoc(){
	  return "We failed contacting an external server";
     }

     public static $error = 507;

     public function getErrorCode(){
	  return self::$error;
     }

     public function __construct($url){
	  parent::__construct("Could not connect to " . $url);
     }
}

/**
 * This class reprents an exception which is thrown when a given method or module is not valid.
 */
class InternalPrinterTDTException extends AbstractTDTException{
     public static function getDoc(){
	  return "Printerfail - Something is wrong in the object";
     }

     public static $error = 508;

     public function getErrorCode(){
	  return self::$error;
     }

     public function __construct($msg){
	  parent::__construct("Object gives weird printeroutput - fix your module: " . $msg);
     }
}

/**
 * This class represents an exception which is trhown when a database related error occurs.
 */
class DatabaseTDTException extends AbstractTDTException{
     public static function getDoc(){
	  return "Something went wrong whilst contacting the database.";
     }

     public static $error = 509;

     public function getErrorCode(){
	  return self::$error;
     }

     public function __construct($msg){
	  parent::__construct("Something went wrong whilst contact the database: " . $msg);
     }
}

/**
 * This class represents an exception which thrown when the creation of a method fails.
 */
class ResourceTDTException extends AbstractTDTException{
     public static function getDoc(){
	  return "Something resource related went wrong.";
     }

     public static $error = 510;

     public function getErrorCode(){
	  return self::$error;
     }

     public function __construct($msg){
	  parent::__construct("Something went wrong: " . $msg);
     }
}

?>