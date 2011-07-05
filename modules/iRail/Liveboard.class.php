<?php
include_once("modules/AMethod.php");
//test php method
class Liveboard extends AMethod{

     private $parameters;
     private $documentation;

     public function __construct(){
     }

     public static function getParameters(){
	  return array("stationId" => "Station id is an id of a station as specified by the iRail API: example: BE.NMBS.0942484");
     }

     public static function getRequiredParameters(){
	  return array("stationId");
     }

     public function setParameters($params){
	  //foreach element in the array, check if the parametershash contains
	  //such a key, if so set the value to the correct key.	  
	  foreach($params as $key=>$value){  	      
	       $this->parameters[$key] = $value;
	  }
     }

     public function call(){
	  $dummyresult = new LiveboardResult();
	  return $dummyresult;
     }
     
     public function allowedPrintMethods(){
	  $printmethods;
	  $printmethods = array("xml");
     }

     public static function getDoc(){
	  return "This is a dummy class, inherits from AMethod";
     }
}

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
     public $levelOfAwesomity = "Level1";
     
     public function __construct(){	 
	 
     }   
}

class Message{

     public $text = "This is a personal message";
     public $size = "42";

     public function __construct(){
	  
     }
     
     
}




?>