<?php
  /* Copyright (C) 2011 by iRail vzw/asbl
   *
   * Author: Jan Vansteenlandt <jan aŧ iRail.be>
   * Author: Pieter Colpaert <pieter aŧ iRail.be>
   * License: AGPLv3
   *
   * Logs a request to a MySQL database
   */

  /**
   * This file contains the RequestLogger.class.php
   * @package The-Datatank/requests
   * @copyright (C) 2011 by iRail vzw/asbl
   * @license AGPLv3
   * @author Jan Vansteenlandt <jan@iRail.be>
   * @author Pieter Colpaert   <pieter@iRail.be>
   */ 
 
  /**
   * This RequestLogger class logs every request to a certain method of a ceratin module.
   * It will use a MySQL database and premade tables to store its data.
   */
class RequestLogger{

     /**
      * This function implements the logging part of the RequestLogger functionality.
      */
     public static function logRequest(){
	  $pageURL = TDT::getPageUrl();	
	  
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
