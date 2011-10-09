<?php
/**
 * This class creates a generic resources. When creating a resource, we always expect a PUT method!
 *
 * @package The-Datatank/model/resources/create
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 * @authot Pieter Colpaert
 */

include_once("ACreator.class.php");
class GenericResourceCreator extends ACreator{

    private $strategy;

    public function __construct($package, $resource, $generic_type){
        parent::__construct($package, $resource);
        // Add the parameters of the strategy!
        $this->generic_type = $generic_type;
        if(!file_exists("model/resources/strategies/" . $this->generic_type . ".class.php")){
            throw new ResourceAdditionTDTException("Generic type does not exist: " . $this->generic_type);
        }
        include_once("model/resources/strategies/" . $this->generic_type . ".class.php");
        // add all the parameters to the $parameters
        // and all of the requiredParameters to the $requiredParameters
        $this->strategy = new $this->generic_type();
    }

    /**
     * This overrides the previous defined required parameters by ACreator. It needs $strategy to be an instance of a strategy. Therefor setParameter needs to have been called upon with a generic_type as argument.
     */
    public function documentParameters(){
        $parameters = parent::documentParameters();
        $parameters["generic_type"]  = "The type of the generic resource.";
        $parameters["documentation"] = "Some descriptional documentation about the generic resource.";
        $parameters = array_merge($parameters,$this->strategy->documentCreateParameters());
        return $parameters;
    }

    /**
     * This overrides the previous defined required parameters by ACreator. It needs $strategy to be an instance of a strategy. Therefor setParameter needs to have been called upon with a generic_type as argument.
     */
    public function documentRequiredParameters(){
        $parameters = parent::documentRequiredParameters();
        $parameters[]= "documentation";
        $parameters[] = "generic_type";
        $parameters = array_merge($parameters,$this->strategy->documentCreateRequiredParameters());
        return $parameters;
    }

    public function setParameter($key,$value){
        // set the correct parameters, to the this class or the strategy we're sure that every key,value passed is correct
        $this->$key = $value;
        if(isset($this->strategy)){
            $this->strategy->$key = $value;
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

        $generic_id= DBQueries::storeGenericResource($resource_id,$this->generic_type,$this->documentation);
        $this->strategy->onAdd($package_id,$generic_id);
    }  
}
?>