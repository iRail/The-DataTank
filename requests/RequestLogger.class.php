<?php
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
	  // get the database object
	  // ATTENTION!!! you have to make sure that the rights for R/W are properly
	  // set for the directory in which the database is stored. 
	  $database = new SQLite3('stats/logging.db',0666);
	  
	  // To conquer sql injection, one must become sql injection.... or use
	  // prepared statements.
	  $stmt = $database->prepare('INSERT INTO requests values( :time, :user_agent, :ip, :url_request)');
	  $stmt->bindValue('time', time());
	  $stmt->bindValue('user_agent',$_SERVER['HTTP_USER_AGENT']);
	  $stmt->bindValue('ip',$_SERVER['REMOTE_ADDR']);
	  $stmt->bindValue('url_request',$pageURL);
	  $result = $stmt->execute();
     }
  }
?>