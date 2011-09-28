<?php
/**
 * This class creates a generic resources
 *
 * @package The-Datatank/model/resources/create
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */

include_once("ACreator.class.php");

/**
 * When creating a resource, we always expect a PUT method!
 */
class GenericResourceCreator extends ACreator{

    private $strategy;

    public function __construct($package, $resource, $resource_type){
        parent::__construct($package, $resource);
        /**
         * Add the parameters
         */
        $this->parameters["generic_type"]  = "The type of the generic resource.";
        $this->parameters["documentation"] = "Some descriptional documentation about the generic resource.";
        
        /**
         * Add the required parameters
         */
        $this->requiredParameters[]= "documentation";
        $this->requiredParameters[] = "generic_type";
    }

    protected function setParameter($key,$value){
        // set the correct parameters, to the this class or the strategy we're sure that every key,value passed is correct
        if($key == "generic_type"){
            // Add the parameters of the strategy!
            $this->generic_type = $value;
            if(!file_exists("model/resources/strategies/" . $this->generic_type . ".class.php")){
                throw new ResourceAdditionTDTException("Generic type does not exist");
            }
            include_once("model/resources/strategies/" . $this->generic_type . ".class.php");
            // add all the parameters to the $parameters
            // and all of the requiredParameters to the $requiredParameters
            $this->strategy = new $this->generic_type();
            $this->parameters = array_merge($this->parameters,$this->strategy->getParameters());
            $this->requiredParameters = array_merge($this->requiredParameters,$this->strategy->getRequiredParameters());
        }else if(isset($this->strategy) && array_key_exists($key,$this->strategy->getParameters()) ){
            $this->strategy->$key = $value;
        }else{
            $this->$key = $value;
        }
    }

    /**
     * execution method
     * Preconditions: 
     * parameters have already been set.
     */
    public function create(){
        /*
         * Create the package and resource entities and create a generic resource entry.
         * Then pick the correct strategy, and pass along the parameters!
         */
        $package_id  = parent::makePackage($this->package);
        $resource_id = parent::makeResource($package_id, $this->resource, "generic");

        $generic_id= DBQueries::storeGenericResource($resource_id,$this->generic_type,$this->documentation,$this->printmethods);
        $this->strategy->onAdd($package_id,$generic_id);
    }
    
    /**
     * get the documentation about the addition of a resource
     */
    public function getCreateDocumentation(){
        return "This class creates a generic resource.";
    }
    
}
?>