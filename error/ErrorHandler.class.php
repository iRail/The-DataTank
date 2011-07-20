<?php
/* Copyright (C) 2011 by iRail vzw/asbl
 *
 * Author: Jan Vansteenlandt <jan aŧ iRail.be>
 * Author: Pieter Colpaert <pieter aŧ iRail.be>
 * License: AGPLv3
 *
 * This is an errorhandler, it will do everything that is expected when an error occured. It will as well save the error to a MySQL database
 */

// this function is called when an unexpected error occurs in receiver.php
function wrapper_handler($number,$string,$file,$line,$context){
     $error_message = $string . " on line " . $line . " in file ". $file . ".";
     $exception = new InternalServerTDTException($error_message);
     ErrorHandler::logException($exception);
     //Exit when we received 1 error. No need to continue
     exit(0);
}

class ErrorHandler{

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
