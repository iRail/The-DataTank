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
            //first check if there resource exists yet
            if ($this->hasResource($package, $resource)) {
                //If it exists, delete it first and continue adding it.
                $this->deleteResource($package, $resource,$RESTparameters);
            }
            
            //if it doesn't, test whether the resource_type has been set
            if (!isset($parameters["resource_type"])) {
                throw new ResourceAdditionTDTException("Parameter resource_type hasn't been set");
            }
            if ($parameters["resource_type"] == "generic" && !isset($parameters["generic_type"])) {
                throw new ResourceAdditionTDTException("Parameter generic_type hasn't been set");
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
            $creator->create();
        } else {
            // all is well, let's create that ontology!
            $creator = $this->factories["ontology"]->createCreator($package, $resource, $parameters, $RESTparameters);
            $creator->create();
        }
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
        //check the parameters for the right updater
        if (!isset($parameters["update_type"])) {
            throw new ParameterTDTException("update_type");
        }
        $updater = new $this->updateActions[$parameters["update_type"]]($package, $resource, $RESTparameters);
        $updater->processParameters($parameters);
        $updater->update();
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
     * To have an idea what's in here, just check yourinstallation/TDTInfo/Resources
     *
     * @return a doc object containing all the packages, resources and further documentation
     */
    public function getAllDoc() {
        $doc = new Doc();
        return $doc->visitAll($this->factories);
    }

    public function getAllAdminDoc() {
        $doc = new Doc();
        return $doc->visitAllAdmin($this->factories);
    }

}

?>
