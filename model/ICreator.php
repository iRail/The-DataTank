<?php
/**
 * Interface for the creation of a resource
 *
 * @package The-Datatank/model
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */
interface ICreator{
    
    /**
     * execution method
     */
    public function create();
    

    /**
     * get all of the parameters to create a resource
     */
    public function getCreateParameters();
    
    /**
     * get the required parameters
     */
    public function getCreateRequiredParameters();
    
    /**
     * get the documentation about the addition of a resource
     */
    public function getCreateDocumentation();

    /**
     * get the optional parameters
     */
    public function getOptionalParameters();
    
}
?>