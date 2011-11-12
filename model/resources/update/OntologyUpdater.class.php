<?php

/**
 * This will add ontological information to a 
 * @package The-Datatank/model/resources/actions
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert
 */
include_once("AUpdater.class.php");

class OntologyUpdater extends AUpdater {

    private $params = array();

    public function __construct($package, $resource, $RESTparameters) {
        parent::__construct($package, $resource, $RESTparameters);
    }

    public function getParameters() {
        return array(
            "update_type" => "...",
            "method" => "The method preferred or add",
            "value" => "Supplied value for the specified method.",
            "namespace" => "The namespace of the value",
            "prefix" => "Possible prefix to add to the namespace",
            "REST" => "temp rest var"
        );
    }

    public function getRequiredParameters() {
        return array("update_type", "method","value","namespace");
    }

    public function getDocumentation() {
        return "This class will update the package ontology";
    }

    protected function setParameter($key, $value) {
        $this->params[$key] = $value;
    }

    public function update() {
        if ($this->resource !== "Ontology")
            throw new OntologyUpdateTDTException("Update only allowed on the resource TDTInfo/Ontology");
        
        //First RESTparameters is the package, rest is the Resource path
        $package = array_shift($this->RESTparameters);
        //Resource path empty? 
        if (count($this->RESTparameters) == 0)
            throw new OntologyUpdateTDTException("Cannot update the ontology of a package, please specify a resource");
        
        //Assemble path
        $path = implode('/', $this->RESTparameters);

        if (!isset($this->params['method']))
            throw new OntologyUpdateTDTException('Method parameter is not set!');

        if (!isset($this->params['value']))
            throw new OntologyUpdateTDTException('Value parameter is not set!');

        if (!isset($this->params['namespace']))
            throw new OntologyUpdateTDTException('Namespace parameter is not set!');

        if (!isset($this->params['namespace']))
            $this->params['prefix'] = null;

        //Do we want to add a mapping, or do we want to set the mapping we prefer to the others
        switch ($this->params['method']) {
            case 'map':
                OntologyProcessor::getInstance()->updatePathMap($package, $path, $this->params['value'], $this->params['namespace'], $this->params['prefix']);
                break;

            case 'prefer':
                OntologyProcessor::getInstance()->updatePathPreferredMap($package, $path, $this->params['value'], $this->params['namespace'], $this->params['prefix']);
                break;

            default:
                throw new OntologyUpdateTDTException('Method ' . $this->params['method'] . ' does not exist!');
        }
    }

}

?>