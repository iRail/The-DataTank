<?php
/* Copyright (C) 2011 by iRail vzw/asbl
 *
 * Author: Jan Vansteenlandt <jan aŧ iRail.be>
 * Author: Pieter Colpaert <pieter aŧ iRail.be>
 * License: AGPLv3
 *
 * Logs a request to a MySQL database
 */

 
 
class RequestLogger{

     public static function logRequest(){
	  $pageURL = 'http';
	  if (!empty($_SERVER['HTTPS'])) {if($_SERVER['HTTPS'] == 'on'){$pageURL .= "s";}}
	  $pageURL .= "://";
	  if ($_SERVER["SERVER_PORT"] != "80") {
	       $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	  } else {
	       $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	  }	
	  
	  // To conquer sql injection, one must become sql injection.... or use
	  // prepared statements.
	   
	  $mysqli = new mysqli('localhost', Config::$MySQL_USER_NAME, Config::$MySQL_PASSWORD, Config::$MySQL_DATABASE);
	  if(mysqli_connect_errno()){
	       printf("Can't connect to MySQL Server. Errorcode: %s\n",mysqli_connect_error());
	       exit();
	  }	
	  // if id = 0, the auto incrementer will trigger
	  $auto_incr = 0;
	  $stmt = $mysqli->prepare("INSERT INTO requests VALUES (?,?,?,?,?)");
	  $seconds = time();
	  $stmt->bind_param('iisss',$auto_incr,$seconds,$_SERVER['HTTP_USER_AGENT'],$_SERVER['REMOTE_ADDR'],$pageURL);
	  $stmt->execute();
	  $stmt->close();
	  $mysqli->close();
     }
  }
?>