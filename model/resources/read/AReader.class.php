<?php

/**
 * Abstract class for reading(fetching) a resource
 *
 * @package The-Datatank/model/resources/read
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */
abstract class AReader {

    public static $BASICPARAMS = array("callback", "filterBy", "filterValue", "filterOp");
    // package and resource are always the two minimum parameters
    protected $parameters = array();
    protected $requiredParameters = array();
    protected $package;
    protected $resource;
    protected $RESTparameters;

    public function __construct($package, $resource, $RESTparameters) {
        $this->package = $package;
        $this->resource = $resource;
        $this->RESTparameters = $RESTparameters;
        $this->getOntology();
    }

    public function getRESTParameters() {
        return $this->RESTparameters;
    }

    /**
     * execution method
     */
    abstract public function read();

    public function processParameters($parameters) {
        /*
         * set the parameters
         */
        foreach ($parameters as $key => $value) {
            $this->setParameter($key, $value);
        }
    }

    abstract protected function setParameter($key, $value);

    protected function getOntology() {
        if (!OntologyProcessor::getInstance()->hasOntology($this->package)) {
             $filename = "custom/packages/" . $this->package . "/" . $this->package . ".ttl";
            if (file_exists($filename))
                OntologyProcessor::getInstance()->readOntologyFile($this->package, $filename);
        } 
    }


}

?>