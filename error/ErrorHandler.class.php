<?php

  /**
 * This is an errorhandler, it will do everything that is expected when an error occured. It will also save the error to a MySQL database.
 * @package The-Datatank/error
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <Jan@iRail.be>
 * @author Pieter Colpaert   <pieter@iRail.be>
 */


  /**
   * This function is called when an unexpected error(non-exception) occurs in receiver.php.
   * @param integer $number Number of the level of the error that's been raised.
   * @param string  $string Contains errormessage.
   * @param string  $file   Contains the filename in which the error occured.
   * @param integer $line   Represents the linenumber on which the error occured.
   * @param string  $context Context is an array that points to the active symbol table at the point the error occurred. In other words, errcontext will contain an array of every variable that existed in the scope the error was triggered in. User error handler must not modify error context.
   */
function wrapper_handler($number,$string,$file,$line,$context){
     $error_message = $string . " on line " . $line . " in file ". $file . ".";
     $exception = new InternalServerTDTException($error_message);
     ErrorHandler::logException($exception);
     //Exit when we received 1 error. No need to continue
     exit(0);
  }

/**
 * This class handles and logs errors and exceptions.
 */
class ErrorHandler{

     /**
      * This functions logs the exception.
      * @param Exception $e Contains an Exception class.
      */
     public static function logException($e){
	  //HTTP Header information
	  header("HTTP/1.1 ". $e->getCode() . " " . $e->getMessage());
	  //In the body, put the message of the error
	  echo $e->getMessage();
	  //and store it to the DB
	  ErrorHandler::WriteToDB($e);
     }

     private static function WriteToDB(Exception $e){
	  $pageURL = TDT::getPageUrl();

	  // To conquer sql injection, one must become sql injection.... or use
	  // prepared statements.	 
	  $mysqli = new mysqli('localhost', Config::$MySQL_USER_NAME, Config::$MySQL_PASSWORD, Config::$MySQL_DATABASE);
	  if(mysqli_connect_errno()){
	       echo "Something went wrong !! . " . mysqli_connect_error();
	       exit();
	  }	
	  // if id = 0, the auto incrementer will trigger
	  $auto_incr = 0;
	  $stmt = $mysqli->prepare("INSERT INTO errors VALUES (?,?,?,?,?,?,?)");
	  $time = time();
	  //echo $auto_incr . ", " . $time . ", " . $_SERVER['HTTP_USER_AGENT'] . ", " .$_SERVER['REMOTE_ADDR']. ", " . $pageURL;
	  $ua = $_SERVER['HTTP_USER_AGENT'];
	  $ip = $_SERVER['REMOTE_ADDR'];
	  $err_message = $e->getDoc();
	  $err_code = $e->getErrorCode();
	  $stmt->bind_param('iisssss',$auto_incr,$time,$ua,$ip,$pageURL,$err_message,$err_code);
	  $stmt->execute();
	  $stmt->close();
	  $mysqli->close();
     }
     
}
?>
