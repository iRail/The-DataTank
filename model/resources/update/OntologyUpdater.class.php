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
            "REST" => "temp rest var"
        );
    }

    public function getRequiredParameters() {
        return array("update_type","method", "value");
    }

    public function getDocumentation() {
        return "This class will update the package ontology";
    }

    protected function setParameter($key, $value) {
        $this->params[$key] = $value;
    }

    public function update() {
        if ($this->resource !== "Ontology")
            throw new ResourceUpdateTDTException("Ontology update is not allowed on this resource");

        switch ($this->params['method']) {
            case 'map':
                
                //this is all temp until the restparameters arrive
                $path = explode('/',RequestURI::getInstance()->getResourcePath());
                $package = array_shift($path);
                $package = array_shift($path);
                $path = implode('/',$path);
                
                OntologyProcessor::getInstance()->updatePathMap($package,$path,$this->params['value']);
                break;
            case 'prefer':
                //OntologyProcessor::getInstance()->updatePathPreferredMap($package,$path);
                break;
            default:
                throw new RdfTDTException('Method ' . $this->params['method'] . ' does not exist!');
        }
    }

}

?>