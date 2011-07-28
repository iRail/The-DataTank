<?php

include_once("modules/AMethod.php");

class CLASSNAME extends AMethod{

	public function __construct(){
		parent::__construct("CLASSNAME");
	}

	public static function getRequiredParameters(){
		return array(); //TODO Add your required parameters here
	}

	public static function getParameters(){
		return array();
		//TODO Add your all your parameters here with documentation!
		// i.e. array(param1=>"x-coordinate",param2=>"y-coordinate");
	}

	public function setParameter($name,$val){

	}

	public static function getDoc(){
		return "TODO Add your documentation about your module here";
	}

	public function call(){
		return null;
		//TODO add your businesslogic here, the resulting object will be formatted in an allowed and preferred print method.
	}

	public static function getAllowedPrintMethods(){
		return array();
	}
}
?>