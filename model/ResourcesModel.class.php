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
include_once("model/Doc.class.php");

class ResourcesModel extends AResourceFactory{

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
        $this->updateActions["foreign_relation"] = "addForeignRelation";
        //Added for linking this resource to a class descibed in an onthology
        $this->updateActions["rdf_mapping"] = "addRdfMapping";
    }

    public static function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new ResourcesModel();
        }
        return self::$instance;
    }
    
    /**
     * Checks the doc whether this exists
     * @return a boolean
     */
    private function hasResource($package,$resource){
        $doc = $this->getAllDoc();
        foreach($doc as $packagename => $resourcenames){
            if($package == $packagename){
                foreach($resourcenames as $resourcename){
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

        $creator = $factories[$restype]->createCreator($package,$resource,$parameters);
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
                $reader->read();
            }
        }
    }

    /**
     * Updates the resource with the given parameters - it will create an updater itself
     */
    public function updateResource($package, $resource, $parameters){ //TOODOOODOOOOO
        //first check if the resource exists
        if(!$this->hasResource($package,$resource)){
            throw new ResourceOrPackageNotFoundTDTException($package,$resource);
        }
        //....
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
     * Uses a visitor to get all docs and return them
     * To have an idea what's in here, just check yourinstallation/TDTInfo/Resources
     *
     * @return a doc object containing all the packages, resources and further documentation
     */
    public function getAllDoc(){
        $doc = new Doc();
        $doc->visitAll($this->factories);
        return $doc;
    }
    
}
?>
