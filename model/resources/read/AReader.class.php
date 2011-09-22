<?php
/**
 * Abstract class for reading(fetching) a resource
 *
 * @package The-Datatank/model/resources/read
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */

include_once("model/IReader.php");

class AReader implements IReader{

    public static $BASICPARAMS = array("callback", "filterBy","filterValue","filterOp");
    // package and resource are always the two minimum parameters
    protected $parameters = array();
    protected $requiredParameters = array();
    protected $optionalParameters = array();
    protected $package;
    protected $resource;

    public function __construct($package,$resource){
        $this->package = $package;
        $this->resource = $resource;
    }
    
    /**
     * execution method
     */
    abstract public function read();

    /**
     * get the optional parameters to get a resource
     * @return Array with the all Read parameters
     */
    public function getReadParameters(){
        return $this->parameters;
    }
    
    /**
     * get the required parameters
     * @return Array with all the required Read parameters
     */
    public function getReadRequiredParameters(){
        return $this->requiredParameters;
    }

    /**
     * get the optional parameters
     * @return Array with all the optional Read parameters
     */
    public function getOptionalParameters(){
        return $this->optionalParameters;
    }
    
    /**
     * get the documentation about getting of a resource
     * @return String with some documentation about the resource
     */
    abstract public function getReadDocumentation();

    /**
     * get the allowed formats
     * @return Array with all of the allowed formatter names
     */
    abstract public function getAllowedFormatters();

    /*
     * get the creation time
     */

    public function getCreationTime(){
        return DBQueries::getCreationTime($this->package,$this->resource);    
    }
    
    /*
     * get the modification time
     */
    public function getModificationTime(){
        return DBQueries::getModificationTime($this->package,$this->resource);
    }
}
?>