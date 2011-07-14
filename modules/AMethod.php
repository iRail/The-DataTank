<?php
/* Copyright (C) 2011 by iRail vzw/asbl
 *
 * Author: Pieter Colpaert <pieter aŧ iRail.be>
 * License: AGPLv3
 *
 * This is an abstract class that needs to be implemented by any method
 */
include_once("error/Exceptions.class.php");
abstract class AMethod{

     public function __construct($classname){
	  // We're going to fetch all parameters and check wether the Required parameters are set. 
	  
	  // Checking the required parameters first
	  foreach($classname::getRequiredParameters() as $key){
	       // If a certain required parameter is not found, throw exception
	       if(!isset($_GET[$key])){
		    throw new ParameterTDTException($key);
	       }
	  }

	  // Now check all GET parameters and give them to setParameter, which needs to be handled by the extended method.
	  foreach($_GET as $key => $value){
	       //the method and module will already be parsed by another system
	       //we don't need the format as well, this is used by printer
	       if($key != "method" && $key != "module" && $key != "format"){
		    //check whether this parameter is in the documented parameters
		    $params = $classname::getParameters();
		    if(isset($params[$key])){
			 $this->setParameter($key,$value);
		    }else{
			 throw new ParameterDoesntExistTDTException($key);
		    }
	       }
	  }
     }

     public static function getRequiredParameters(){
	  return array();
     }

     public static function getParameters(){
	  return array();
     }
     
     public static function getDoc(){
	  echo "I'm undocumented Q_Q";
     }

     abstract public function call();

     abstract public function setParameter($name,$val);

     abstract public function allowedPrintMethods();

}
class Object{
}
?>