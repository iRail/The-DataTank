<?php

include_once("modules/AMethod.php");
class MyHouse extends AMethod{

	public function __construct(){
	     parent::__construct("MyHouse");
	}

	public static function getRequiredParameters(){
	     return array(); 
	}

	public static function getParameters(){
	     return array();
	}

	public function setParameter($name,$val){
	     
	}

	public static function getDoc(){
	     return "This class returns a house object which implements the locatable interface.<br/>This way we can test our kml printer";
	}

	public function call(){
	     $o = new stdClass();
	     $o->house = new stdClass();
	     $o->house->location = new Location(3.14158265,51.13142,"My House","description");
	     return $o;
	}
	
	public static function getAllowedPrintMethods(){
	     return array("kml", "xml","json", "jsonp");
	}
	
}
?>