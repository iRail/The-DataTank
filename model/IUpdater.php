<?php
/**
 * Interface to update a resource
 *
 * @package The-Datatank/model
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */
Interface IUpdater {
 
    /**
     * execution method
     */
    public function update();

    /**
     * process the all of the parameters
     */
    public function processParamaters();

    /**
     * get the optional parameters to update a resource
     */
    public function getUpdateParameters();
    
    /**
     * get the required parameters
     */
    public function getUpdateRequiredParameters();

    /**
     * get the optional parameters
     */
    public function getOptionalParameters();

    /**
     * get the documentation about updating a resource
     */
    public function getUpdateDocumentation();    
}
?>