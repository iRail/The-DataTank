<?php
/**
 * Interface for a factory: check documentation on the Abstract Factory Pattern if you don't understand this code.
 *
 * @package The-Datatank/model
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert
 */

abstract class AResourceFactory{

    /**
     * @return an object with all documentation of all packages and resources. It can be used directly for 
     */
    public function getAllDocs(){
	$docs = new StdClass();
	foreach($this->getAllResourceNames() as $package => $resources){
	    $docs->$package = new StdClass();
	    foreach($resources as $resource){
		$docs->$package->$resource = new StdClass();
		$docs->$package->$resource->doc = $this->getResourceDoc($package,$resource);
		$docs->$package->$resource->requiredparameters = $this->getResourceRequiredParameters($package,$resource);
		$docs->$package->$resource->parameters = $this->getResourceParameters($package,$resource);
		$docs->$package->$resource->formats = $this->getAllowedPrintMethods($package,$resource);
	    }
	}
	return $docs;
    }

    /************************************************GETTERS*****************************************************************/

    /**
     * @return returns a string containing the documentation about the resource. It returns an empty string when the resource could not be found
     */
    abstract public function getResourceDoc($package, $resource);

    /**
     * @return returns an associative array with the documentation for each parameter for a specific resource 
     */
    abstract public function getResourceParameters($package, $resource);

    /**
     * @return returns an array with all required parameters
     */
    abstract public function getResourceRequiredParameters($package,$resource);

    /**
     * @return returns an array with all possible printers
     */
    abstract public function getAllowedPrintMethods($package,$resource);

    /**
     * @return returns whether the Factory can return a resource
     */
     abstract public function hasResource($package, $resource);
    
    /**
     * @return gets an instance of a AResource class.
     */
     abstract public function getResource($package, $resource);

     /**
      * @return an associative array with all packages as keys, and arrays of resources as values 
      */
     abstract public function getAllResourceNames();

     /************************************************SETTERS*****************************************************************/
     
     /**
      * Removes an entire resource
      */
     abstract public function deleteResource($package,$resource);
     
     /**
      * Deletes all resources in a package
      */
     abstract public function deletePackage($package);
     
     /**
      * Add a resource to a (existing/non-existing) package
      */
     abstract public function addResource($package,$resource, $content);
     
     /**
      * If the package/resource exists, then update the resource with the content provided
      */
     abstract public function updateResource($package,$resource,$content);

     /**
      * Add ontology-information to the specific resource
      */
     //abstract public function addOntologyInformation($package, $resource, $RESTid, $object);
}

?>
