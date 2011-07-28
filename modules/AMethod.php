<?php

/**
 * This is an abstract class that needs to be implemented by any method
 *
 * @package The-Datatank/modules
 * @license AGPLv3
 * @author Pieter Colpaert   <pieter@iRail.be>
 * @author Jan Vansteenlandt <jan@iRail.be>
 */

include_once("properties/Location.class.php");
include_once("properties/Time.class.php");

/**
 * This class is an abstract class that represent a php method that can be called. It provides certain
 * functionality, yet method specific functionality still needs to be implement by inheriting classes.
 */
abstract class AMethod{

     private static $BASICPARAMS = array("callback", "module","method","format");

     /**
      * Constructor of AMethod
      * @param string $classname Contains the classname, needed to check if i.e. all the correct parameters are passed along.
      */
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
	       if(!in_array($key,self::$BASICPARAMS)){
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

     /**
      * This function returns the required parameters for this method in order to function correctly.
      * @return Array with the names of the required parameters.
      */
     public static function getRequiredParameters(){
	  return array();
     }

     /**
      * This function returns all parameters that this call can use.
      * @return Array with the names of all the parameters.
      */
     public static function getParameters(){
	  return array();
     }
     
     /**
      * This function returns the names of all the allowed formats in which a resulting object can be
      * printed.
      * @return Array with the names of the allowed print formats for this method.
      */
     public static function getAllowedPrintMethods(){
	  return array();
     }

     /**
      * This function returns the documentation of this method.
      * @return String with the documentation of the method.
      */
     public static function getDoc(){
	  echo "I'm undocumented Q_Q";
     }

     /**
      * This functions contains the businesslogic of the method
      * @return Object representing the result of the businesslogic.
      */
     abstract public function call();

     /**
      * This function is used to set a value for a certain parameter.
      * @param string $name Name of the parameter
      * @param mixed  $val  Value that will be set to the parameter.
      */
     abstract public function setParameter($name,$val);

}

?>