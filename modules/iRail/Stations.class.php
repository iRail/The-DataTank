<?php
/* Copyright (C) 2011 by iRail vzw/asbl
 *
 * Author: Pieter Colpaert <pieter aŧ iRail.be>
 * License: AGPLv3
 *
 * Lists all stations for a certain system
 */

include_once("modules/AMethod.php");

class Stations extends AMethod{

     private $lang,$system = "NMBS";

     public function __construct(){
	  parent::__construct("Stations");
     }

     public static function getParameters(){
	  return array("lang" => "Language for the stations",
		       "system" => "The name of the public transport company: for instance De Lijn, or NMBS"
	       );
     }

     public static function getRequiredParameters(){
	  return array();
     }

     public function setParameter($key,$val){
	  if($key == "lang"){
	       $this->lang = $val;
	  }

	  if($key == "system"){
	       if(in_array($val,iRailTools::ALLOWED_SYSTEMS)){
		    $this->system = $val;
	       }
	  }
     }

     public function call(){
	  return new Station();
     }
     
     public function allowedPrintMethods(){
	  return array("xml", "json", "php");
     }

     public static function getDoc(){
	  return "Stations will return a list of all known stops of a system";
     }
}

class Station{
     
}

?>