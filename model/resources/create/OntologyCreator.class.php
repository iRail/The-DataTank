<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of OntologyCreator
 *
 * @author mvdrsand
 */
include_once("ACreator.class.php");

class OntologyCreator extends ACreator {

    private $RESTparameters;
    private $resource_type;

    function __construct($package, $resource, $RESTparameters) {
        parent::__construct($package, $resource, $RESTparameters);
        $this->RESTparameters = $RESTparameters;
    }

    public function create() {
        $package = array_shift($this->RESTparameters);

        if (count($this->RESTparameters) == 0) {
            //Create empty ontology for a package
            OntologyProcessor::getInstance()->createOntology($package,$this->ontology_file);
        }else if(count($this->RESTparameters) == 1){
            //Add class entry for resource in empty resource
            OntologyProcessor::getInstance()->createClassPath($package,$this->RESTparameters[0]);
        } else {
            //Create ontology for a package and describe the given path
            if (!isset($this->resource_type))
                throw new ResourceAdditionTDTException("Ontology: Type of resource is not specified");

            $resource = implode("/", $this->RESTparameters);
            
            //Check the PUT parameter type to 
            if ($this->resource_type == "property")
                OntologyProcessor::getInstance()->createPropertyPath($package, $resource);
            else if ($this->resource_type == "class")
                OntologyProcessor::getInstance()->createClassPath($package, $resource);
            else
                throw new ResourceAdditionTDTException("Ontology: Type of resource is not correct");
        }
    }

    public function setParameter($key, $value) {
        if ($key == "type")
            $this->resource_type = $value;
    }

    public function documentParameters() {
        $parameters = parent::documentParameters();
        $parameters["package"] = "The package for which you want to create an ontology";
        $parameters["resource"] = "The resource for which you want to create a description";
        $parameters = array_merge($parameters, $this->strategy->documentCreateParameters());
        return $parameters;
    }

    public function documentRequiredParameters() {
        $parameters = parent::documentRequiredParameters();
        $parameters[] = "package";
        $parameters = array_merge($parameters, $this->strategy->documentCreateRequiredParameters());
        return $parameters;
    }

}

?>
