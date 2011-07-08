<?php
include_once("modules/AMethod.php");

class Queries extends AMethod{

     public function __construct(){
	  parent::__construct("Queries");
     }

     public static function getParameters(){
	  return array();
     }

     public static function getRequiredParameters(){
	  return array();
     }

     public function setParameter($key,$val){

     }

     public function call(){
	  $q = new QueryResults();
	  $q->visitsPerDay = array ('1310039116000'=>5,'1310125515000'=>28);
	  return  $q;
     }
     
     public function allowedPrintMethods(){
	  return array("xml", "json", "php");
     }

     public static function getDoc(){
	  return "Get's some basic analysis results.";
     }
}

class QueryResults{
     public $visitsPerDay;
}

?>