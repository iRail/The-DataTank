<?php
  /* Copyright (C) 2011 by iRail vzw/asbl */
  /* 
   * Author: Pieter Colpaert <pieter aŧ iRail.be>
   * License: AGPLv3
   *
   * This is an errorhandler, it will do everything that is expected when an error occured.
   */

class ErrorHandler{

     public static function logException($e){
	  //comment if in productionmode
	  echo $e->getMessage();
	  
	  // get the full request url
	  //echo "ERROR OCCURED, trying to log";
	  
	  $pageURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
	  if ($_SERVER["SERVER_PORT"] != "80"){
	       $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	  } 
	  else{
	       $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	  }
	  // get the database object
	  // ATTENTION!!! you have to make sure that the rights for R/W are properly
	  // set for the directory in which the database is stored. 
	  $database = new SQLite3('stats/logging.db',0666);
	  
	  // To conquer sql injection, one must become sql injection.... or use
	  // prepared statements.
	  $stmt = $database->prepare('INSERT INTO errors values( :time, :user_agent, :ip, :url_request, :error_message, :error_code)');
	  $stmt->bindValue('time', time());
	  $stmt->bindValue('user_agent',$_SERVER['HTTP_USER_AGENT']);
	  $stmt->bindValue('ip',$_SERVER['REMOTE_ADDR']);
	  $stmt->bindValue('url_request',$pageURL);
	  $stmt->bindValue('error_message',$e->getMessage());
	  $stmt->bindValue('error_code',$e->getErrorCode());
	  $result = $stmt->execute();
	  // if the execute failed $result will contain FALSE, otherwise it'll return 
	  // an object.	  
     }
     

  }
?>