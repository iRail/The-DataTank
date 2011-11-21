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

    public function __construct($package, $resource, $RESTparameters) {
        $this->package = $package;
        $this->resource = $resource;
        $this->RESTparameters = $RESTparameters;
    }

    /**
     * process the parameters
     */
    public function processParameters($parameters) {
        unset($parameters["update_type"]);
        
        foreach ($parameters as $key => $value) {
            //check whether this parameter is in the documented parameters
            if (!in_array($key,array_keys($this->getParameters()))) {
                throw new ParameterDoesntExistTDTException($key);
            }
            $this->setParameter($key, $value);
        }
    }

    /**
     * execution method
     */
    abstract public function update();

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
