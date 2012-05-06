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
include_once("model/OntologyFactory.class.php");
include_once("model/Doc.class.php");
include_once("resources/update/OntologyUpdater.class.php");
include_once("resources/update/GenericResourceUpdater.class.php");

class ResourcesModel {

    private static $instance;
    private $factories; //array of factories
    private $updateActions;

    private function __construct() {

        $this->factories = array(); //(ordening does matter here! Put the least expensive on top)
        $this->factories["generic"] = new GenericResourceFactory();
        $this->factories["core"] = new CoreResourceFactory();
        $this->factories["remote"] = new RemoteResourceFactory();
        $this->factories["installed"] = new InstalledResourceFactory();
        $this->factories["ontology"] = new OntologyFactory();

        /*
         * This array maps all the update types to the correct delegation methods
         * these methods are methods that are part of the resourcemodel, but are not
         * part of the resource itself. i.e. a foreign relation between two resources
         */
        $this->updateActions = array();
        //Added for linking this resource to a class descibed in an onthology
        $this->updateActions["ontology"] = "OntologyUpdater";
        $this->updateActions["generic"] = "GenericResourceUpdater";


    }

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new ResourcesModel();
        }
        return self::$instance;
    }

    /**
     * Checks if a package exists
     */
    public function hasPackage($package){
        $doc = $this->getAllDoc();
        foreach ($doc as $packagename => $resourcenames) {
            if ($package == $packagename) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks the doc whether a certain resource exists in our system.
     * We will look for a definition in the documentation. Of course,
     * the result of the documentation visitor class will be cached
     * 
     * @return a boolean
     */
    public function hasResource($package, $resource) {
        $doc = $this->getAllDoc();
        foreach ($doc as $packagename => $resourcenames) {
            if ($package == $packagename) {
                foreach ($resourcenames as $resourcename => $var) {
                    if ($resourcename == $resource) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Checks if we are trying to CRUD an Ontology
     * 
     * @return boolean
     */
    public function checkOntology($package, $resource, $RESTparameters) {
        //Check if it is an ontology
        return $package == "TDTInfo" && $resource == "Ontology" && count($RESTparameters) > 0;
    }

    /**
     * Creates the given resource
     * @param string $package The package name under which the resource will exist.
     * @param string $resource The resource name under which the resource will be called.
     * @param array $parameters An array with create parameters
     * @param array $RESTparameters An array with additional RESTparameters
     */
    public function createResource($package, $resource, $parameters, $RESTparameters) {
        
        //If we want to CRUD ontology, handle differently
        if (!$this->checkOntology($package, $resource, $RESTparameters)) {
            //if it doesn't, test whether the resource_type has been set
            if (!isset($parameters["resource_type"])) {
                throw new ResourceAdditionTDTException("Parameter resource_type hasn't been set");
            }

            /**
             * adding some semantics to the resource_type parameter
             * generic/generic_type should be parsed as generic being the resource_type and generic_type as the
             * generic type, without passing that as a separate parameter
             * NOTE that passing generic/generic_type has priority over generic_type = ...
             */

            $resourceTypeParts = explode("/",$parameters["resource_type"]);
            if($resourceTypeParts[0] != "remote"){    
                if ( $resourceTypeParts[0] == "generic" && !isset($parameters["generic_type"]) 
                     && isset($resourceTypeParts[1])) {
                    $parameters["generic_type"] = $resourceTypeParts[1];
                    $parameters["resource_type"] = $resourceTypeParts[0];
                }else if(!isset($parameters["generic_type"])){
                    throw new ResourceAdditionTDTException("Parameter generic_type hasn't been set, or the combination generic/generic_type hasn't been properly passed. A template-example is: generic/CSV");
                }
            }

            $restype = $parameters["resource_type"];
            //now check if the file exist and include it
            if (!in_array($restype, array("generic", "remote"))) {
                throw new ResourceAdditionTDTException("Resource type doesn't exist. Choose from generic or remote");
            }
            // get the documentation containing information about the required parameters
            $doc = $this->getAllAdminDoc();
            
            /**
             * get the correct requiredparameters list to check
             */
            $resourceCreationDoc;
            if ($restype == "generic") {
                /*
                 * Issue: keys of an array cannot be gotten without an exact match, csv != CSV is an example
                 * of a result of this matter, this however should be ==
                 * Solution : fetch all the keys, compare them strtoupper ( or lower, matter of taste ) , then replace 
                 * generic_type with the "correct" one
                 */
                $parameters["generic_type"] = $this->formatGenericType($parameters["generic_type"],$doc->create->generic);
                $resourceCreationDoc = $doc->create->generic[$parameters["generic_type"]];
            } else { // remote
                $resourceCreationDoc = $doc->create->remote;
            }

            /**
             * Check if all required parameters are being passed
             */
            foreach ($resourceCreationDoc->requiredparameters as $key) {
                if (!isset($parameters[$key])) {
                    throw new RequiredParameterTDTException("Required parameter " . $key . " has not been passed");
                }
            }
           
            //now check if there are nonexistent parameters given
            foreach (array_keys($parameters) as $key) {
                if (!in_array($key, array_keys($resourceCreationDoc->parameters))) {
                    throw new ParameterDoesntExistTDTException($key);
                }
            }

            // all is well, let's create that resource!
            $creator = $this->factories[$restype]->createCreator($package, $resource, $parameters, $RESTparameters);
            try{
                //first check if there resource exists yet
                if ($this->hasResource($package, $resource)) {
                    //If it exists, delete it first and continue adding it.
                    //It could be that because errors occured after the addition, that
                    //the documentation reset in the CUDController isn't up to date anymore
                    //This will result in a hasResource() returning true and deleteResource returning false (error)
                    //This is our queue to reset the documentation.
                    try{
                        $this->deleteResource($package, $resource,$RESTparameters);
                    }catch(Exception $ex){
                        //Clear the documentation in our cache for it has changed        
                        $c = Cache::getInstance();
                        $c->delete(Config::$HOSTNAME . Config::$SUBDIR . "documentation");
                        $c->delete(Config::$HOSTNAME . Config::$SUBDIR . "admindocumentation");
                        throw new InternalServerTDTException("Error: ". $ex->getMessage() . " We've done a hard reset on the internal documentation, try adding it again. If this doesn't work please log on issue or e-mail one of the developers.");
                    }
                }
            }catch(Exception $ex){
                //Clear the documentation in our cache for it has changed        
                $c = Cache::getInstance();
                $c->delete(Config::$HOSTNAME . Config::$SUBDIR . "documentation");
                $c->delete(Config::$HOSTNAME . Config::$SUBDIR . "admindocumentation");
                $this->deleteResource($package, $resource,$RESTparameters);
                throw new Exception($ex->getMessage());
            }
            $creator->create();
        } else {
            // all is well, let's create that ontology!
            $creator = $this->factories["ontology"]->createCreator($package, $resource, $parameters, $RESTparameters);
            $creator->create();
        }
    }

    /**
     * Searches for a generic entry in the generic- create part of the documentation, independent of 
     * how it is passed (i.e. csv == CSV )
     * @return The correct entry in the generic table ( csv would be changed with CSV )
     */
    private function formatGenericType($genType, $genericTable){
        foreach($genericTable as $type => $value){
            if(strtoupper($genType) == strtoupper($type)){
                return $type;
            }
        }
        throw new ResourceAdditionTDTException($genType . " was not found as a generic_type");
    }
    

    /**
     * Reads the resource with the given parameters
     * @param string $package The package name under which the resource exists.
     * @param string $resource The resource name.
     * @param array $parameters An array with read parameters
     * @param array $RESTparameters An array with additional RESTparameters
     */
    public function readResource($package, $resource, $parameters, $RESTparameters) {
        //first check if the resource exists
        if (!$this->hasResource($package, $resource)) {
            throw new ResourceOrPackageNotFoundTDTException($package, $resource);
        }

        foreach ($this->factories as $factory) {
            if ($factory->hasResource($package, $resource)) {
                $reader = $factory->createReader($package, $resource, $parameters, $RESTparameters);
                return $reader->execute();
            }
        }
    }


    /**
     * Updates the resource with the given parameters.
     * @param string $package The package name
     * @param string $resource The resource name
     * @param array $parameters An array with update parameters
     * @param array $RESTparameters An array with additional RESTparameters
     */
    public function updateResource($package, $resource, $parameters, $RESTparameters) {
        //first check if the resource exists
        if (!$this->hasResource($package, $resource)) {
            throw new ResourceOrPackageNotFoundTDTException($package, $resource);
        }
        
        /**
         * Get the resource properties from the documentation
         * Replace that passed properties and re-add the resource
         */
        $doc = $this->getAllDescriptionDoc();
        $currentParameters = $doc->$package->$resource;

        /** issue with updates:
         * not all things you see are primary put parameters, some are derived and can't be update
         * i.e. doc property of a remote resource, that property hasn't been put but has been derived from 
         * the other properties of a remote resource.
         * currently hard coded because there are no extensive units of abstract descriptions (generic, remote) yet...
         */

        unset($currentParameters->parameters);
        unset($currentParameters->requiredparameters);
        unset($currentParameters->remote_package);
        unset($currentParameters->doc);
        unset($currentParameters->resource);

        foreach($parameters as $parameter => $value){
            if($value != "" && $parameter != "columns"){
                $currentParameters->$parameter = $value;
            }
        }
        
        /**
         * Columns aren't key = value datamembers and will be handled separatly
         */
        if(isset($currentParameters->columns) && isset($parameters["columns"])){
            foreach($parameters["columns"] as $index => $value){
                $currentParameters->columns[$index] = $value;
            }
        }

        // delete the empty parameters from the currentParameters object
        foreach((array)$currentParameters as $key => $value){
            if($value == ""){
                unset($currentParameters->$key);
            }
        }

        $currentParameters = (array)$currentParameters;
        $this->createResource($package,$resource,$currentParameters,array());
        
        /**
         * DO NOT DELETE the snippet below, might be necessary for Ontology-addition, awaiting reply of Miel Van der Sande
         */
        /*$updater = new $this->updateActions[$parameters["update_type"]]($package, $resource, $RESTparameters);
          $updater->processParameters($parameters);
          $updater->update();*/
    }

    /**
     * Deletes a Resource
     * @param string $package The package name
     * @param string $resource The resource name
     * @param array $parameters An array with delete parameters
     * @param array $RESTparameters An array with additional RESTparameters
     */
    public function deleteResource($package, $resource, $RESTparameters) {
        //If we want to DELETE ontology, handle differently
        if (!$this->checkOntology($package, $resource, $RESTparameters)) {

            //first check if the resource exists
            if (!$this->hasResource($package, $resource)) {
                throw new ResourceOrPackageNotFoundTDTException("package/resource couple " . $package . "/" . $resource . " not found.");
            }
            
            /**
             * We only support the deletion of generic and remote resources and packages by 
             * an API call.
             */
            $factory = "";

            if ($this->factories["generic"]->hasResource($package, $resource)) {
                $factory = $this->factories["generic"];
            } else if ($this->factories["remote"]->hasResource($package, $resource)) {
                $factory = $this->factories["remote"];
            } else {
                throw new DeleterTDTException($package . "/" . $resource);
            }
            $deleter = $factory->createDeleter($package, $resource, $RESTparameters);
            $deleter->delete();
        } else {
            $deleter = $this->factories["ontology"]->createDeleter($package, $resource, $RESTparameters);
            $deleter->delete();
        }
        //Clear the documentation in our cache for it has changed        
        $c = Cache::getInstance();
        $c->delete(Config::$HOSTNAME . Config::$SUBDIR . "documentation");
        $c->delete(Config::$HOSTNAME . Config::$SUBDIR . "admindocumentation");
    }

    /**
     * Deletes all Resources in a package
     * @param string $package The packagename that needs to be deleted.
     */
    public function deletePackage($package) {
        $d = $this->getAllDoc();
        if (isset($d->$package)) {
            $resources = $d->$package;
            foreach ($d->$package as $resource => $documentation) {
                if ($resource != "creation_date") {
                    $this->deleteResource($package, $resource, array());
                }
            }
            DBQueries::deletePackage($package);
        } else {
            throw new ResourceOrPackageNotFoundTDTException($package . " is not an existing package.");
        }
    }

    /**
     * Uses a visitor to get all docs and return them
     * To have an idea what's in here, just check yourinstallationfolder/TDTInfo/Resources
     * @return a doc object containing all the packages, resources and further documentation
     */
    public function getAllDoc() {
        $doc = new Doc();
        return $doc->visitAll($this->factories);
    }

    public function getAllDescriptionDoc(){
        $doc = new Doc();
        return $doc->visitAllDescriptions($this->factories);
    }

    public function getAllAdminDoc() {
        $doc = new Doc();
        return $doc->visitAllAdmin($this->factories);
    }

    /**
     * Will only return an id if the key has an active status !!!!
     */
    public function getAPIId($key){
        $result = DBQueries::getAPIId($key);
        return $result;
    }

    public function isKeyAuthorized($api_key_id,$package,$resource){
        $resourceId = DBQueries::getResourceIdByName($package,$resource);
        $status = DBQueries::getApikeyStatus($api_key_id);
        $result = NULL;
        if($status == "active"){
            $result = DBQueries::getAccessEntry($resourceId,$api_key_id);
        }
        
        
        return $result != NULL && $result != 0;
    }
}
?>





