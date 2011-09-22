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
        //Added for linking this resource to a class descibed in an onthology
        $this->updateActions["rdf_mapping"] = "addRdfMapping";
    }

    public function getResourceType($package,$resource){
        foreach($this->factories as $factorytype => $factory){
            if($factory->hasResource($package,$resource)){
                return $factorytype;
            }
        }   
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

    /**
     * @return the creation time of a certain resource
     */
    public function getCreationTime($package,$resource){
        foreach($this->factories as $factory){
            if($factory->hasResource($package,$resource)){
                return $factory->getCreationTime($package,$resource);
            }
        }
    }

    /**
     * @return the modification time of a certain resource
     */
    public function getModificationTime($package,$resource){
        foreach($this->factories as $factory){
            if($factory->hasResource($package,$resource)){
                return $factory->getModificationTime($package,$resource);
            }
        }
    }
    
    /*
     * @ return an array with every package + the creation timestamp of the package
     */
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
    

    public function getExtra($package,$resource){
        foreach($this->factories as $factory){
            if($factory->hasResource($package,$resource)){
                $f = $factory;
                break;
            }
        }
        return $f->getExtra($package,$resource);
    }

    public function addResource($package,$resource, $content){
        //We have to get at least a parameter resource_type
        if(!isset($content["resource_type"])){
            throw new ParameterTDTException("resource_type");
        }
        //validation of add parameters: resource type
        $resource_type = $content["resource_type"];
        if(!isset($this->factories[$resource_type])){
            throw new ResourceAdditionTDTException("Resource type $resource_type does not exist");
        }

        //if package/resource already exists, don't add it! Throw an error instead
        if($this->hasResource($package,$resource)){
            throw new ResourceAdditionTDTException("$package/$resource already exists");
        }

        //create fitting resource factory for a given resource type
        $factory = $this->factories[$resource_type];

        //create/fetch package
        $package_id = parent::makePackageId($package);

        //create the resource entry, or throw an exception package/resource already exists
        parent::makeResourceId($package_id,$resource,$resource_type);

        //Add the rest of the specific information for that type of resource
        $factory->addResource($package,$resource,$content);
    }

    /**
     * Check if the given update type is a supported one
     * if so execute the proper update method
     * @param $package packagename
     * @param $resource resourcename
     * @param $content the POST parameters
     * @param $resURI unique resource URI for adding semantics
     */
    public function updateResource($package,$resource,$content,$resURI=null){
        if(isset($content["update_type"]) && isset($this->updateActions[$content["update_type"]])){
            $method = $this->updateActions[$content["update_type"]];
            $this->$method($package,$resource,$content,$resURI);
        }else{
            throw new ResourceUpdateTDTException($content["update_type"] ." is not a supported update type.");
        }
    }

    private function addForeignRelation($package,$resource,$content){
        $foreignRelation = new ForeignRelation();
        $foreignRelation->update($package,$resource,$content);
    }
    
    //Supplies RDFMapper with post variables
    private function addRdfMapping($package,$resource,$content){
        $rdfmapper = new RDFMapper();
        //need full path for adding semantics!!
        $resource = RequestURI::getInstance()->getRealWorldObjectURI();
        $rdfmapper->update($package,$resource,$content);
    }
}
?>
