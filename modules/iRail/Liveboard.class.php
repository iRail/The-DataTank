<?php
include_once("modules/AMethod.php");
//test php method
class Liveboard extends AMethod{

     private $parameters;
     private $documentation;
     

     public function __construct(){
	  //echo "constructing Liveboard";
	  $this->parameters[] = "stationId";
     }

     public function getParameters(){
	  //echo "Getting the required parameters.";
	  return $this->parameters;
     }

     public function setParameters($params){
	  //foreach element in the array, check if the parametershash contains
	  //such a key, if so set the value to the correct key.
	  foreach($array as $key=>$value){
	       if(array_key_exists($key, $this->parameters)){
		    $this->parameters[$key] = $value;
		    
	       }	       
	  }	  
     }

     public function call(){
	  return "LIVEBOARD OBJECT";
     }
     
     public function allowedPrintMethods(){
	  $printmethods;
	  $printmethods = array("xml");
     }

     public function getDoc(){
	  return "This is a dummy class, inherits from AMethod";
     }
     
     
}

?>