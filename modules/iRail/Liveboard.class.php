<?php
/* Copyright (C) 2011 by iRail vzw/asbl */
/**
 * Author: Jan Vansteenlandt <vansteenlandt.jan@gmail.com>
 * License: AGPLv3
 *
 * This file is currently being used for testpurposes. It returns some testdata we use for our analysis tool. (stats.php)
 */
include_once("modules/AMethod.php");
include_once("modules/iRail/Stations.class.php");
include_once("modules/iRail/iRailTools.class.php");

class Liveboard extends AMethod{

     private $lang,$system;     

     public function __construct(){
	  parent::__construct("Liveboard");
     }

     public static function getParameters(){
	  return array("station" => "This is a name of a station or an ID as specified by the iRail API: example: BE.NMBS.0942484",
		       "time" => "time of the requested liveboard concatenated. The 3pm would be: 1500",
		       "direction" => "Do you want to have the arrivals or the departures. Values: arrival or departures",
		       "lang" => "Language for the stations",
		       "system" => "The name of the public transport company: for instance De Lijn, or NMBS"
	       );
     }

     public static function getRequiredParameters(){
	  return array("station");
     }

     public function setParameter($key,$val){
	  if($key == "lang"){
	       $this->lang = $val;
	  }
	  if($key == "system" && in_array($val, iRailTools::ALLOWED_SYSTEMS)){
	       $this->system = $val;
	  }
     }

     public function call(){
	  $dummyresult = new LiveboardResult();
	  return $dummyresult;
     }
     
     public function allowedPrintMethods(){
	  return array("xml", "json", "php");
     }

     public static function getDoc(){
	  return "Liveboard will return the next arrivals or departures in a station.";
     }
}
/* Classes needed to return some testdata */
class LiveboardResult{
	  public $message;
	  public $sender;
	  public function __construct(){
	       $this->sender = new Person();
	       $this->message = new Message();	       
	  }
}

class Person{
     public $name = "Core";
     public $age = "42";
     public $nicknames=  array('Patrick','Coretrick');
     
     public function __construct(){
     }   
}

class Message{

     public $text = "This is a personal message";
     public $md   = "bbeaacc44f8a4419e085b091dc8190ff";

     public function __construct(){
	  
     }    
}
?>