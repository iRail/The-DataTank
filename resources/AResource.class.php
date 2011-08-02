<?php
/**
 * This is an abstract class that needs to be implemented by any method
 *
 * @package The-Datatank/resources
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
abstract class AResource{

    public static $BASICPARAMS = array("callback", "format");

    /**
     * This function need to be called to set all parameters correctly
     */
    public function processParameters(){
	// Check all GET parameters and give them to setParameter, which needs to be handled by the extended method.
	foreach($_GET as $key => $value){
	    //the method and module will already be parsed by another system
	    //we don't need the format as well, this is used by printer
	    if(!in_array($key,self::$BASICPARAMS)){
		//check whether this parameter is in the documented parameters
		$params = $this->getParameters();
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
    abstract public function getRequiredParameters();

    /**
     * This function returns all parameters that this call can use.
     * @return Array with the names of all the parameters plus documentation
     */
    abstract public function getParameters();
    
     
    /**
     * This function returns the names of all the allowed formats in which a resulting object can be
     * printed.
     * @return Array with the names of the allowed print formats for this method.
     */
    abstract public function getAllowedPrintMethods();
    

    /**
     * This function returns the documentation of this method.
     * @return String with the documentation of the method.
     */
    public function getDoc(){
	echo "I'm undocumented";
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