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
     * @return an object with all documentation of all packages and resources.
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
                $docs->$package->$resource->timestamp = $this->getCreationTime($package,$resource);
                $extra = $this->getExtra($package,$resource);                
                if(!is_null($extra)){
                    $docs->$package->$resource->extra = $extra;
                }
	    }
	}
	return $docs;
    }

    public function getExtra($package,$resource){
        return NULL;    
    }
    
    
    

    /**
     * @return an object with all existing packages
     */
    public function getAllPackages(){
        $result = new StdClass();
        $packages = array();
        foreach($this->getAllPackages() as $package){
            $packages[] = $package;
        }
        $result->packages = $packages;
        return $result;
    }
    

    /**
     * This creates a resource ID for a certain resource/package pair.
     * @param resource Name of the resource
     * @param package_id ID of the package (you can resolve this with getPackageId($packagename))
     * @param resource_type generic or remote
     * @return the ID of the added resource to the DB.
     */
    public function makeResourceId($package_id,$resource,$resource_type){
        
        $checkExistence = R::getAll(
            "SELECT resource.id
             FROM resource, package
             WHERE :package_id = package.id and resource.resource_name =:resource and resource.package_id = package.id",
            array(":package_id" => $package_id, ":resource" => $resource)
        );
        

        if(sizeof($checkExistence) == 0){
            $newResource = R::dispense("resource");
            $newResource->package_id = $package_id;
            $newResource->resource_name = $resource;
            $newResource->creation_timestamp = time();
            $newResource->last_update_timestamp = time();
            $newResource->type = $resource_type;
            return R::store($newResource);
        }
        return $checkExistence[0]["id"];
    }

    /**
     * Creates an Id for a package if it didn't exist yet and it will return it 
     * @param name of a package
     * @return id of a package
     */
    public function makePackageId($package){
        $result = R::getAll(
            "SELECT package.id as id 
             FROM package 
             WHERE package_name=:package_name",
            array(":package_name"=>$package)

        );
        
        if(sizeof($result) == 0){
            $newpackage = R::dispense("package");
            $newpackage->package_name = $package;
            $newpackage->timestamp = time();
            $id = R::store($newpackage);
            return $id;
        }
        return $result[0]["id"];
        
    }



    /*********************************************ABSTRACT***GETTERS*****************************************************************/

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

     /**
      * @return the creation timestamp of a resource
      */
     public function getCreationTime($package,$resource){    
         return DBQueries::getCreationTime($package,$resource); 
     }
     
     
     

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
}
?>
