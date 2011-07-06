<?php
ini_set("include_path",".:modules/iRail/");
include_once("modules/AMethod.php");

class Stations extends AMethod{

     private $lang,$system;

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
	  $dummyresult = new LiveboardResult();
	  return $dummyresult;
     }
     
     public function allowedPrintMethods(){
	  $printmethods = array("xml");
     }

     public static function getDoc(){
	  return "Stations will return a list of all known stops of a system";
     }
}

?>