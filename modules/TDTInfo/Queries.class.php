<?php
  /* Copyright (C) 2011 by iRail vzw/asbl
   *
   * Author: Jan Vansteenlandt <jan aŧ iRail.be>
   * Author: Pieter Colpaert <pieter aŧ iRail.be>
   * License: AGPLv3
   *
   * Lists the number of queries to the API per day
   */

include_once("modules/AMethod.php");

class Queries extends AMethod{

     private $module; // must be set! Contains the value of the module that needs to be analysed.
     private $method; // if set only look at certain data from a certain method within the given module.
     private $errors = ""; // if set, get data from errors table. ( not set or true )
     private $queryResults;
     

     public function __construct(){
	  parent::__construct("Queries");
     }

     public static function getParameters(){
	  return array("mod" => "Name of a module that needs to be analysed, must be set !",
		       "meth" => "Name of a method within the given module, is not required.",
		       "err" => "If set then the analysis will get it's data from the error table if not from the request table."
	       );
     }

     public static function getRequiredParameters(){
	  return array("mod");
     }

     public function setParameter($key,$val){
	  if($key == "mod"){
	       $this->module = $val;
	  }elseif($key == "meth"){
	       $this->method = $val;
	  }elseif($key == "err"){
	       $this->errors = $val;
	  }
     }

     public function getData(){

	  /* Connect to mysql database */
	  $link = mysqli_connect(
	       'localhost',                    /* The host to connect to */
	       Config::$MySQL_USER_NAME,       /* The user to connect with the MySQL database */
	       Config::$MySQL_PASSWORD,        /* The password to use to connect with the db  */
	       Config::$MySQL_DATABASE);                     /* The default database to query */

	  if (!$link) {
	       printf("Can't connect to MySQL Server. Errorcode: %s\n", mysqli_connect_error());
	       exit;
	  }

          /* Send a query to the server */
	 
	  if($this->errors == ""){
	       $databasetable = "requests";
	  }else{
	       $databasetable = "errors";
	  }	  

	  $queryString = 'SELECT count(1) as amount, time FROM '
	       . $databasetable . ' WHERE url_request REGEXP \''. $this->module . '/'.$this->method .
	       '\' group by from_unixtime(time,\'%D %M %Y\')';
	  //echo "queryString is ". $queryString;
	  
	  if ($result = mysqli_query($link,$queryString)) {   

	       /* Fetch the results of the query */
	       $this->queryResults = new QueryResults();
	       
	       while( $row = mysqli_fetch_assoc($result) ){
		    $amount = $row['amount'];
		    $time  = $row['time'];
		    $this->queryResults->result[$time] = $amount;
	       }

	       /* Destroy the result set and free the memory used for it. */
	       mysqli_free_result($result);
	  }

          /* Close the connection */
	  mysqli_close($link);
     }

     public function call(){
	  $this->getData();
	  return $this->queryResults;
     }
     
     public function allowedPrintMethods(){
	  return array("json","xml");
     }

     public static function getDoc(){
	  return "Lists the number of queries(requests/errors) to this datatank instance per day";
     }
}

class QueryResults{
     public $result;
}
?>