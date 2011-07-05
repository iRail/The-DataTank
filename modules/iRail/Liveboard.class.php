<?php
include_once("modules/AMethod.php");
//test php method
class Liveboard extends AMethod{

     private $parameters;
     private $documentation;
     

     public function __construct(){	  
	  $this->parameters[] = "stationId";
     }

     public function getParameters(){
	  return $this->parameters;
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

     public function getDoc(){
	  return "This is a dummy class, inherits from AMethod";
     }
}

class LiveboardResult{
	  public $message = "This is a personal and private message.";
	  public $sender;
	  public function __construct(){
	       $this->sender = new Person();
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
     
     
}




?>