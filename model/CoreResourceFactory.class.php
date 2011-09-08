<?php
/**
 * This class will handle all resources needed by the core. For instance the resources provided by the TDTInfo package.
 *
 * @package The-Datatank/model/
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert
 */

class CoreResourceFactory extends AResourceFactory{

    /**
     * This function loads a resource if not yet included. On fail throw an error.
     */
    private function includeResource($package,$resource){
	if($this->hasResource($package,$resource)){
	    include_once("model/packages/$package/$resource.class.php");
	}else{
	    throw new ResourceOrPackageNotFoundTDTException($package,$resource);
	}
    }
    
	
    /**
     * @return returns a string containing the documentation about the resource. It returns an empty string when the resource could not be found
     */
    public function getResourceDoc($package, $resource){
	$this->includeResource($package,$resource);	
	return $resource::getDoc();
    }

    /**
     * @return returns an associative array with the documentation for each parameter for a specific resource 
     */
    public function getResourceParameters($package, $resource){
	$this->includeResource($package,$resource);
	return $resource::getParameters();	
    }

    /**
     * @return returns an array with all required parameters
     */
    public function getResourceRequiredParameters($package,$resource){
	$this->includeResource($package,$resource);
	return $resource::getRequiredParameters();
    }

    /**
     * @return a boolean if resource exists
     */
    public function hasResource($package,$resource){
	return file_exists("model/packages/" . $package . "/" . $resource . ".class.php");
    }
 
    public function getAllowedPrintMethods($package,$resource){
	$this->includeResource($package,$resource);
	return $resource::getAllowedPrintMethods();
    }
    
    /**
     * Scans the folder modules for other resources
     * @return an array containing all the resourcenames available
     */
    public function getAllResourceNames(){
	$packages = array("TDTInfo" => array("Resources", "Queries"), "Feedback" => array("Messages"));
	return $packages;
    }
    
    /**
     * @return gets an instance of a AResource class.
     */
    public function getResource($package, $resource){
	include_once("model/packages/" . $package . "/" . $resource . ".class.php");
	return new $resource($package,$resource);
    }

    
    /***********************************************SETTERS******************************************************/

    public function deletePackage($package){
        //do nothing - you cannot delete an installed package. You can however uninstall it by removing the folder through ssh/ftp/...
    }

    public function deleteResource($package,$resource){
        //do nothing
    }

    /**
     * Add a resource to a (existing/non-existing) package
     */
    public function addResource($package,$resource, $content){
        //cannot be called upon, throw exception
    }
     
    /**
     * If the package/resource exists, then update the resource with the content provided
     */
    public function updateResource($package,$resource,$content){
        //cannnot be called, throw exception
    }

}

?>
