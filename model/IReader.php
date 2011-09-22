<?php
/**
 * Interface for getting a resource
 *
 * @package The-Datatank/model
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */
Interface IReader{
 
    /**
     * execution method
     */
    public function read();
    

    /**
     * get the optional parameters to get a resource
     */
    public function getReadParameters();
    
    /**
     * get the required parameters
     */
    public function getReadRequiredParameters();

    /**
     * get the optional parameters
     */
    public function getOptionalParameters();

    /**
     * get the documentation about getting of a resource
     */
    public function getReadDocumentation();

    /**
     * get the allowed formats
     */
    public function getAllowedFormatters();

    /*
     * get the creation time
     */
    public function getCreationTime();
    
    /*
     * get the modification time
     */
    public function getModificationTime();
}
?>