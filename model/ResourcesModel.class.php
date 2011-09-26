<?php
/**
 * This is the model for our application. You can access everything from here
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
include_once("model/Doc.class.php");
include_once("resources/update/RdfMapping.class.php");
include_once("resources/update/ForeignRelation.class.php");

class ResourcesModel{

    private static $instance;

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
        $this->updateActions["foreign_relation"] = "ForeignRelation";
        //Added for linking this resource to a class descibed in an onthology
        $this->updateActions["rdf_mapping"] = "RdfMapping";
    }

    public static function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new ResourcesModel();
        }
        return self::$instance;
    }
    
    /**
     * Checks the doc whether a certain resource exists in our system.
     * We will look for a definition in the documentation. Of course,
     * the result of the documentation visitor class will be cached
     * @return a boolean
     */
    public function hasResource($package,$resource){
        $doc = $this->getAllDoc();
        foreach($doc as $packagename => $resourcenames){
            if($package == $packagename){
                foreach($resourcenames as $resourcename => $var){
                    if($resourcename == $resource){
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Creates the given Resource
     */ 
    public function createResource($package, $resource, $parameters){
        //first check if there resource exists yet
        if($this->hasResource($package,$resource)){
            throw new ResourceAdditionTDTException($package . "/" . $resource . " - It exists already");
        }
        //if it doesn't, test whether the resource_type has been set
        if(!isset($parameters["resource_type"])){
            throw new ResourceAdditionTDTException("Parameter resource_type hasn't been set");
        }
        $restype = $parameters["resource_type"];
        //now check if the file exist and include it
        if(!in_array($restype, array("generic", "remote"))){
            throw new ResourceAdditionTDTException("Resource type doesn't exist. Choose from generic or remote");
        }

        $creator = $this->factories[$restype]->createCreator($package,$resource,$parameters);
        $creator->create();
    }
    
    /**
     * Reads the resource with the given parameters
     */
    public function readResource($package, $resource, $parameters){
        //first check if the resource exists
        if(!$this->hasResource($package,$resource)){
            throw new ResourceOrPackageNotFoundTDTException($package,$resource);
        }
        foreach($this->factories as $factory){
            if($factory->hasResource($package, $resource)){
                $reader = $factory->createReader($package,$resource,$parameters);
                return $reader->read();
            }
        }
    }

    /**
     * Updates the resource with the given parameters - it will create an updater itself
     */
    public function updateResource($package, $resource, $parameters){
        //first check if the resource exists
        if(!$this->hasResource($package,$resource)){
            throw new ResourceOrPackageNotFoundTDTException($package,$resource);
        }
        //check the parameters for the right updater
        if(!isset($parameters["update_type"])){
            throw new ParameterTDTException("update_type");
        }
        $updater = new $this->updateActions[$parameters["update_type"]]($package,$resource);
        $updates->processParameters($parameters);
        $updater->update();
    }
    
    /**
     * Deletes a Resource
     */
    public function deleteResource($package, $resource){
        //first check if the resource exists
        if(!$this->hasResource($package,$resource)){
            throw new ResourceOrPackageNotFoundTDTException($package,$resource);
        }
        $deleter = $factory[$restype]->createDeleter($package,$resource);
        $deleter->delete();
    }

    /**
     * Deletes all Resources in a package
     */
    public function deletePackage($package){
        $d = $this->getAllDoc();
        foreach(get_object_vars($d->$package) as $resource){
            $this->deleteResource($package, $resource);
        }
    }

    /**
     * Uses a visitor to get all docs and return them
     * To have an idea what's in here, just check yourinstallation/TDTInfo/Resources
     *
     * @return a doc object containing all the packages, resources and further documentation
     */
    private $doc;
    public function getAllDoc(){
        if(!isset($this->doc)){
            $doc = new Doc();
            $this->doc = $doc->visitAll($this->factories);
        }
        return $this->doc;
    }
    
}
?>
