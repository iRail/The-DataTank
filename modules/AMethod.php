<?php
abstract class AMethod{

     public function __construct($classname){
	  //We're going to fetch all parameters and check wether the Required parameters are set.
	  //when an unknown parameter is returned, 
	  
	  //check required params first
	  foreach($classname::getRequiredParameters() as $key){
	       //if a certain required parameter is not found, throw exception
	       if(!isset($_GET[$key])){
		    throw new ParameterTDTException($key);
	       }
	  }

	  //now check all GET parameters and give them to setParameter, which needs to be handled by the extended method
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
	  echo "I'm undocumented :(";
     }

     abstract public function call();

     abstract public function setParameter($name,$val);
     
     abstract public function allowedPrintMethods();
}

?>