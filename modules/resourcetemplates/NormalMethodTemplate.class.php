<?php

include_once("resources/AResource.class.php");

class CLASSNAME extends AResource{

	public function __construct(){
		parent::__construct("CLASSNAME");
	}

	public static function getRequiredParameters(){
		return array(ALLPARAMETERS); 
	}

	public static function getParameters(){
		return array(PARAMETERS);
	}

	public function setParameter($name,$val){
		//TODO add your logic to assign certain values to your keys from the httprequest
	}

	public static function getDoc(){
		return "DOCUMENTATION";
	}

	public function call(){
		//TODO add your businesslogic here, the resulting object will be formatted in an allowed and 
	        //preferred print method.
		return null;
	}

	public static function getAllowedPrintMethods(){
		return array(FORMATS);
	}
}
?>