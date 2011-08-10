<?php
/**
 * Interface for a factory: check documentation on the Factory Method Pattern if you don't understand this code.
 *
 * Each factory should be a singleton! 
 *
 * @package The-Datatank/factories
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert
 */

abstract class AResourceFactory{

    /**
     * @return an object with all documentation of all modules and resources. It can be used directly for 
     */
    public function getAllDocs(){
	$docs = new StdClass();
	foreach($this->getAllResourceNames() as $module => $resources){
	    $docs->$module = new StdClass();
	    foreach($resources as $resource){
		$docs->$module->$resource = new StdClass();
		$docs->$module->$resource->doc = $this->getResourceDoc($module,$resource);
		$docs->$module->$resource->requiredparameters = $this->getResourceRequiredParameters($module,$resource);
		$docs->$module->$resource->parameters = $this->getResourceParameters($module,$resource);
		$docs->$module->$resource->formats = $this->getAllowedPrintMethods($module,$resource);
	    }
	}
	return $docs;
    }

    /**
     * @return returns a string containing the documentation about the resource. It returns an empty string when the resource could not be found
     */
    abstract public function getResourceDoc($module, $resource);

    /**
     * @return returns an associative array with the documentation for each parameter for a specific resource 
     */
    abstract public function getResourceParameters($module, $resource);

    /**
     * @return returns an array with all required parameters
     */
    abstract public function getResourceRequiredParameters($module,$resource);

    /**
     * @return returns an array with all possible printers
     */
    abstract public function getAllowedPrintMethods($module,$resource);

    /**
     * @return returns whether the Factory can return a resource
     */
     abstract public function hasResource($module, $resource);
    
    /**
     * @return gets an instance of a AResource class.
     */
     abstract public function getResource($module, $resource);

     /**
      * @return an associative array with all modules as keys, and arrays of resources as values 
      */
     abstract public function getAllResourceNames();
     
}

?>
