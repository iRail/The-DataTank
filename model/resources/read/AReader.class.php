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

    public static $BASICPARAMS = array("callback", "filterBy", "filterValue", "filterOp", "page");
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
    }

    /**
     * Gets the REST parameters of the request
     * @return array with the REST parameters.
     */
    public function getRESTParameters() {
        return $this->RESTparameters;
    }

    /**
     * Execution method of a reader, which reads a resource
     * @return StdClass of a datasource
     */
    public function execute() {
        return $this->read();
    }


    /**
     * read method of a resource
     */
    abstract public function read();

    /**
     * Processes the parameters necessary to read a certain resource
     * @param array $parameters An array with the parameters passed with the GET request.
     */
    public function processParameters($parameters) {
        /*
         * set the parameters
         */
        foreach ($parameters as $key => $value) {
            $this->setParameter($key, $value);
        }
    }

    abstract protected function setParameter($key, $value);


}

?>
