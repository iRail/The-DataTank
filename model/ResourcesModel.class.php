<?php
/**
 * Will combine all other factories in 1 factory! This is the model for Controller.class.php.
 *
 * @package The-Datatank/model
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert
 */

include_once("model/GenericResourceFactory.class.php");
include_once("model/InstalledResourceFactory.class.php");
include_once("model/RemoteResourceFactory.class.php");
include_once("model/CoreResourceFactory.class.php");

class ResourcesModel extends AResourceFactory{

    private static $uniqueinstance;

    private $factories;//array of factories

    private function __construct(){
	$this->factories = array(); //(ordening does matter here! Put the least expensive on top)
	$this->factories["generic"] = new GenericResourceFactory();
        $this->factories["core"] = new CoreResourceFactory();
	$this->factories["remote"] = new RemoteResourceFactory();
	$this->factories["installed"] = new InstalledResourceFactory();
    }
    
    public static function getInstance(){
	if(!isset(self::$uniqueinstance)){
	    self::$uniqueinstance = new ResourcesModel();
	}
	return self::$uniqueinstance;
    }

    /**
     * @return returns a string containing the documentation about the resource. It returns an empty string when the resource could not be found
     */
    public function getResourceDoc($package, $resource){
	foreach($this->factories as $factory){
	    if($factory->hasResource($package,$resource)){
		return $factory->getResourceDoc($package,$resource);
	    }
	}
	//if not really any factory has the resource, throw an exception
	throw new ResourceOrPackageNotFoundTDTException($package . "/" .$resource);
    }

    /**
     * @return returns an associative array with the documentation for each parameter for a specific resource 
     */
    public function getResourceParameters($package, $resource){
	foreach($this->factories as $factory){
	    if($factory->hasResource($package,$resource)){
		return $factory->getResourceParameters($package,$resource);
	    }
	}
	//if not really any factory has the resource, throw an exception
	throw new ResourceOrPackageNotFoundTDTException($package . "/" .$resource);
    }

    /**
     * @return returns an array with all required parameters
     */
    public function getResourceRequiredParameters($package,$resource){
	foreach($this->factories as $factory){
	    if($factory->hasResource($package,$resource)){
		return $factory->getResourceRequiredParameters($package,$resource);
	    }
	}
	//if not really any factory has the resource, throw an exception
	throw new ResourceOrModuleNotFoundTDTException($package . "/" .$resource);
    }
    
    public function getAllowedPrintMethods($package,$resource){
	foreach($this->factories as $factory){
	    if($factory->hasResource($package,$resource)){
		return $factory->getAllowedPrintMethods($package,$resource);
	    }
	}
	//if not really any factory has the resource, throw an exception
	throw new ResourceOrModuleNotFoundTDTException($package . "/" .$resource);
    }

    /**
     * @return an array containing all the resourcenames available
     */
    public function getAllResourceNames(){
	$rn = array();
	foreach($this->factories as $factory){
	    foreach($factory->getAllResourceNames() as $package => $resourcenames){
		if(isset($rn[$package])){
		    $rn[$package] = array_merge($rn[$package],$resourcenames);
		}else{
		    $rn[$package] = $resourcenames;
		}	
	    }
	}
	return $rn;
    }


    public function hasResource($package,$resource){
	foreach($this->factories as $factory){
	    if($factory->hasResource($package,$resource)){
		return true;
	    }
	}
	return false;
    }    

    /**
     * @return gets an instance of a AResource class.
     */
    public function getResource($package,$resource){
	//find the one who has the resource!
	foreach($this->factories as $factory){
	    if($factory->hasResource($package,$resource)){
		return $factory->getResource($package,$resource);
	    }
	}
	throw new ResourceOrPackageNotFoundTDTException($package . "/" . $resource);
    }

    /*****************************************SETTERS****************************************/
    public function deleteResource($package,$resource){
        foreach($this->factories as $factory){
            if($factory->hasResource($package,$resource)){
                /*
                 * deletes specific resource type
                 */
                $factory->deleteResource($package,$resource);
                /*
                 * also delete resource entry in resource table
                 */
                
                $result = R::exec(
                    "DELETE FROM resource 
                     WHERE resource.resource_name=:resource and package_id IN
                                      (SELECT id FROM package WHERE package_name=:package)",
                    array(":package" => $package, ":resource" => $resource)
                );
                
                break;
            }
        }    
    }
    

    public function deletePackage($package){
        //delete all resources in every factory
        foreach($this->factories as $factory){
            $factory->deletePackage($package);
        }
        //now also delete the package-entry in the db

        $deleteResourceEntries = R::exec(
            "DELETE FROM resource 
                     WHERE package_id IN
                                      (SELECT id FROM package WHERE package_name=:package)",
            array(":package" => $package)
        );

         $deletePackage = R::exec(
            "DELETE FROM package WHERE package_name=:package",
            array(":package" => $package)
        );
    }
    
    public function addResource($package,$resource, $content){
        //validation of add parameters
        if(!isset($content["resource_type"])){
            throw new ParameterTDTException("resource_type");
        }

        $resource_type = $content["resource_type"];
        if(!isset($this->factories[$resource_type])){
            throw new ResourceAdditionTDTException("Resource type $resource_type does not exist");
        }

        /*
         * create fitting resource factory
         */
        $factory = $this->factories[$resource_type];

        /*
         * create/fetch package
         */
        $package_id = $this->makePackageId($package);

        /*
         * create the resource entry, or throw an exception package/resource already exists
         */
        $this->makeResourceId($resource,$package_id,$resource_type);

        /*
         * Add the rest of the specific information for that type of resource
         */
        $factory->addResource($package,$resource,$content);
    }


    public function makePackageId($package){
        /* 
         * will return the ID of the package. If package does not exists, it will be added
         * if we're requesting an installed or core package, it doesn't matter since the package is not resourcetype dependant
         * so adding it won't do any harm
         */
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
        }else{
            return $result[0]["id"];
        }
    }

    public function makeResourceId($resource,$package_id,$resource_type){
        /* 
         * will return the ID of the resource. If resource doesn't exist, it will be added
         * this resource doesn't have a type specified yet! It just contains the name, and a FK to a package
         * So if we see that there's already package-resource pair, we throw an exception.
         */
        $checkExistence = R::getAll(
            "SELECT resource.id
             FROM resource, package
             WHERE :package_id = package.id and resource_name =:resource and package_id = package.id",
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
        }else{
            throw new ResourceAdditionTDTException("package/resource already exists");
        }
    }

    /*
     * This function is meant to be called by resource factories
     * In our business logic the resource ($resource) will already exist 
     * so we can expect a valid return id. If the resource doesn't exist, throw an error
     * This is different from makeResourceId because in make Resource, we want to create a 
     * fresh resource, and prohibit overriding resources !
     */
    public function getResourceId($package_id,$resource){
        $getId = R::getAll(
            "SELECT resource.id as res_id
             FROM   resource,package
             WHERE  resource_name =:resource and package.id = :package_id",
            array(":resource" => $resource, ":package_id" => $package_id)
        );
        if(sizeof($getId) == 0){
            throw new ResourceAdditionTDTException("Resource hasn't been created yet.");
        }else{
            return $getId[0]["res_id"];
        }
    }
    
    public function updateResource($package,$resource,$content){
        foreach($this->factories as $factory){
            if($factory->hasResource($package,$resource)){
                $factory->updateResource($package,$resource,$content);
                break;
            }
        }
        
    }
}
?>
