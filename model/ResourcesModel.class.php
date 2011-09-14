<?php
/**
 * Will combine all other factories in 1 factory! This is the model for Controller.class.php.
 *
 * @package The-Datatank/model
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert
 * @author Jan Vansteenlandt
 */

include_once("model/AResourceFactory.class.php");
include_once("model/GenericResourceFactory.class.php");
include_once("model/InstalledResourceFactory.class.php");
include_once("model/RemoteResourceFactory.class.php");
include_once("model/CoreResourceFactory.class.php");

include_once("model/resources/actions/ForeignRelation.class.php");
include_once("model/DBQueries.class.php");

class ResourcesModel extends AResourceFactory{

    private static $uniqueinstance;

    private $factories;//array of factories
    private $updateActions;
    
    private function __construct(){

	$this->factories = array(); //(ordening does matter here! Put the least expensive on top)
	$this->factories["generic"]   = new GenericResourceFactory();
        $this->factories["core"]      = new CoreResourceFactory();
	$this->factories["remote"]    = new RemoteResourceFactory();
	$this->factories["installed"] = new InstalledResourceFactory();

        /*
         * This array maps all the update types to the correct delegation methods
         * these methods are methods that are part of the resourcemodel, but are not
         * part of the resource itself. i.e. a foreign relation between two resources
         */
        $this->updateActions = array();
        $this->updateActions["foreign_relation"] = "addForeignRelation";
    }
    
    public static function getInstance(){
        if(!isset(self::$uniqueinstance)){
            self::$uniqueinstance = new ResourcesModel();
        }
        return self::$uniqueinstance;
    }

    /**
     * @return returns a string containing the documentation about the resource. 
     * It returns an empty string when the resource could not be found
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

    public function getAllPackages(){
        $backendpackages = DBQueries::getAllPackages();
        $installedpackages = $this->factories["installed"]->getAllPackages();
        $corepackages =  $this->factories["core"]->getAllPackages();
        $merge = array_merge($installedpackages,$backendpackages,$corepackages);
        return $merge;
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
                DBQueries::deleteResource($package, $resource);
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
        DBQueries::deletePackageResources($package);
        DBQueries::deletePackage($package);

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
         * create fitting resource factory for a given resource type
         */
        $factory = $this->factories[$resource_type];

        /*
         * create/fetch package
         */
        $package_id = parent::makePackageId($package);

        /*
         * create the resource entry, or throw an exception package/resource already exists
         */
        parent::makeResourceId($resource,$package_id,$resource_type);

        /*
         * Add the rest of the specific information for that type of resource
         */
        $factory->addResource($package,$resource,$content);
    }

    public function updateResource($package,$resource,$content){
        /*
         * Check if the given update type is a supported one
         * if so execute the proper update method
         */
        
        if(isset($this->updateActions[$content["update_type"]])){
            $method = $this->updateActions[$content["update_type"]];
            $this->$method($package,$resource,$content);
        }else{
            throw new ResourceUpdateTDTException($content["update_type"] ." is not a supported update type.");
        }
    }

    private function addForeignRelation($package,$resource,$content){
        $foreignRelation = new ForeignRelation();
        $foreignRelation->update($package,$resource,$content);
    }
}
?>
