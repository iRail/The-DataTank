<?php
/**
 * Abstract class to delete a resource
 *
 * @package The-Datatank/model/resources/delete
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */

abstract class ADeleter{
    
    protected $package;
    protected $resource;
    
    public function __construct($package,$resource, $RESTparameters){
        $this->package = $package;
        $this->resource = $resource;
        $this->RESTparameters = $RESTparameters;
    }
    

    /**
     * execution method
     */
    abstract public function delete();
    
}
?>