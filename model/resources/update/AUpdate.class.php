<?php
/**
 * Abstract class to update a resource
 *
 * @package The-Datatank/model/resources/update
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */

include_once("model/IUpdater.php");

abstract class AUpdater implements IUpdater{
 
    protected $package;
    protected $resource;
    protected $parameters = array();
    protected $requiredParameters = array();
    protected $optionalParameters = array();
    
    public function __construct($package,$resource){
        $this->package = $package;
        $this->resource = $resource;
    }

    /**
     * process the parameters
     */
    public function processCreateParameters($parameters){
        // process every parameters passed along with the creation requests
        // and assign them to the correct parameter belonging to the Creator-class
        $allowedParameters = array_keys($this->parameters);
	foreach($allowedParameters as $key => $value){
            //check whether this parameter is in the documented parameters
            if(isset($this->requiredParameters[$key])){
                $this->optionalParameters[$key] = $value;
            }else if(isset($this->optionalParameters[$key])){
                $this->requiredParameters[$key] = $value;
            }else{
                throw new ParameterDoesntExistTDTException($key);
            }
        }
        // check if all requiredparameters have been set
       
        foreach($this->requiredParameters as $key => $value){
            if($value == ""){
                throw new ParameterTDTException("Required parameter ".$key ." has not been passed");
            }
        }
    }    

    /**
     * execution method
     */
    abstract public function update();

    /**
     * get the optional parameters to update a resource
     */
    public function getUpdateParameters(){
        return $this->parameters;
    }
    
    /**
     * get the required parameters
     */
    public function getUpdateRequiredParameters(){
        return $this->requiredParameters;
    }
    
    /**
     * get the optional parameters
     */
    public function getOptionalParameters(){
        return $this->optionalParameters;
    }
    
    /**
     * get the documentation about updating a resource
     */
    public function getUpdateDocumentation();    
}
?>