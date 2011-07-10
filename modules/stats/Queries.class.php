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
	  return "Lists the number of queries to this datatank instance per day";
     }
}

class QueryResults{
     public $visitsPerDay;
}
?>