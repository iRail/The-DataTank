<?php

include_once("modules/AMethod.php");
include_once("House.php");
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
		return "This class returns a house object which implements the locatable interface".
		     "This way we can test our kml printer";
	}

	public function call(){
	     return new House();
	}
	
	public static function getAllowedPrintMethods(){
	     return array("kml");
	}
	
}
?>