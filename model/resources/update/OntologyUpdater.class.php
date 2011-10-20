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
        return array("update_type", "method");
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

        $package = array_shift($this->RESTparameters);
        $path = implode('/', $this->RESTparameters);

        if (!isset($this->params['method']))
            throw new RdfTDTException('Method parameter is not set!');


        switch ($this->params['method']) {
            case 'map': {
                    if (!isset($this->params['value']))
                        throw new RdfTDTException('Value parameter is not set!');
                    OntologyProcessor::getInstance()->updatePathMap($package, $path, $this->params['value']);
                    break;
                }
            case 'prefer': {
                    if (!isset($this->params['value']))
                        throw new RdfTDTException('Value parameter is not set!');
                    OntologyProcessor::getInstance()->updatePathPreferredMap($package, $path, $this->params['value']);
                    break;
                }

            //Temporary solution. Should ne able to PUT and DELETE to do this
            case 'delete': {
                    OntologyProcessor::getInstance()->deletePath($package, $path);
                    break;
                }
            case 'create': {
                    OntologyProcessor::getInstance()->createPath($package, $path);
                    break;
                }
            default: {
                    throw new RdfTDTException('Method ' . $this->params['method'] . ' does not exist!');
                }
        }
    }

}

?>