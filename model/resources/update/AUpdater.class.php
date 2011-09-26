<?php

/**
 * Abstract class to update a resource
 *
 * @package The-Datatank/model/resources/update
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */
abstract class AUpdater {

    protected $package;
    protected $resource;
    protected $parameters = array();
    protected $requiredParameters = array();

    public function __construct($package, $resource) {
        $this->package = $package;
        $this->resource = $resource;

        //Miel: Added this for not having an ParameterDoesntExistTDTException on POST variable update_type
        $this->parameters['update_type'] = '';
    }

    /**
     * process the parameters
     */
    public function processParameters($parameters) {
        foreach ($parameters as $key => $value) {
            //check whether this parameter is in the documented parameters
            if (!isset($this->parameters[$key])) {
                throw new ParameterDoesntExistTDTException($key);
            } else if (in_array($key, $this->requiredParameters)) {
                $this->$key = $value;
            }
        }

        /*
         * check if all requiredparameters have been set
         */
        foreach ($this->requiredParameters as $key) {
            if ($this->$key == "") {
                throw new ParameterTDTException("Required parameter " . $key . " has not been passed");
            }
        }

        /*
         * set the parameters
         */
        foreach ($parameters as $key => $value) {
            $this->setParameter($key, $value);
        }

    }

    /**
     * execution method
     */
    abstract public function update();

    /**
     * get the optional parameters to update a resource
     */
    public function getParameters() {
        return $this->parameters;
    }

    /**
     * get the required parameters
     */
    public function getRequiredParameters() {
        return $this->requiredParameters;
    }

    /**
     * set the parameter
     */
    abstract protected function setParameter($key, $value);

    /**
     * get the documentation about updating a resource
     */
    abstract public function getDocumentation();
}

?>