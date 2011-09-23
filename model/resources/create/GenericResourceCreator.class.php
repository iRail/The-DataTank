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

    public function __construct($resource_type){
        parent::__construct();
        /**
         * Add the parameters
         */
        $this->parameters["generic_type"]  = "The generic type of the generic resource.";
        $this->parameters["documentation"] = "Some documentation about the generic resource.";
        $this->parameters["printmethods"]  = "The allowed formats in which the resulting object may be represented in.";
        
        /**
         * Add the required parameters
         */
        $this->requiredParameters["documentation"] = "";
        $this->requiredParameters["printmethods"] = "";
        $this->requiredParameters["generic_type"] = $resource_type;

        /**
         * Add the parameters of the strategy!
         */ 
        if(!file_exists("model/resources/strategies/" . $resource_type . ".class.php")){
            throw new ResourceAdditionTDTException("Generic type does not exist");
        }
        include_once("model/resources/strategies/" . $resource_type . ".class.php");
        // add all the parameters to the $parameters
        // and all of the requiredParameters to the $requiredParameters
        $strategy = new $resource_type();
        $this->parameters[] = $strategy->getParameters();
        $this->requiredParameters = array_merge($this->requiredParameters,$strategy->getRequiredParameters());
        $this->optionalParameters = array_merge($this->optionalParameters,$strategy->getOptionalParameters());
        
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
        $package_id  = parent::makePackage();
        $resource_id = parent::makeResource($package_id);        

        $generic_type  = $this->requiredParameters["generic_type"];
        $documentation = $this->requiredParameters["documentation"];
        $printmethods  = $this->requiredParameters["printmethods"];

        $generic_id =  DBQueries::storeGenericResource($resource_id,$generic_type,$documentation,$printmethods);
        $allParams = array_merge($this->requiredParameters,$this->optionalParameters);
        $strategy->onAdd($package_id,$generic_id,$allParams);
    }
    
    /**
     * get the documentation about the addition of a resource
     */
    public function getCreateDocumentation(){
        return "This class creates a generic resource.";
    }
    
}
?>