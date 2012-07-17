<?php
/**
 * This class will handle all resources installed in de package directory
 *
 * @package The-Datatank/model
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan a t iRail.be>
 */

include_once("model/resources/create/InstalledResourceCreator.class.php");
include_once("model/resources/delete/InstalledResourceDeleter.class.php");

class InstalledResourceFactory extends AResourceFactory{
    
    public function createCreator($package,$resource, $parameters, $RESTparameters){
        $creator = new InstalledResourceCreator($package,$resource, $RESTparameters);
        foreach($parameters as $key => $value){
            $creator->setParameter($key,$value);
        }
        return $creator;
    }
    
    public function createReader($package,$resource, $parameters, $RESTparameters){
        $location = $this->getLocationOfResource($package,$resource);
        if(file_exists("custom/packages/" . $location)){
            include_once("custom/packages/" . $location);
            $classname = $this->getClassnameOfResource($package,$resource);
            $creator = new $classname($package,$resource, $RESTparameters);
            $creator->processParameters($parameters);
            return $creator;
        }else{
            throw new CouldNotGetDataTDTException("custom/packages/".$location);
        }
    }

    public function hasResource($package,$resource){
        $resource = DBQueries::hasInstalledResource($package, $resource);
        return isset($resource["present"]) && $resource["present"] >= 1;   
    }
    

    public function createDeleter($package,$resource, $RESTparameters){
        include_once("model/resources/delete/InstalledResourceDeleter.class.php");
        $deleter = new InstalledResourceDeleter($package,$resource, $RESTparameters);
        return $deleter;
    }

    public function makeDoc($doc){
        //ask every resource we have for documentation
        foreach($this->getAllResourceNames() as $package => $resourcenames){
            if(!isset($doc->$package)){
                $doc->$package = new StdClass();
            }

            foreach($resourcenames as $resourcename){
                $location = $this->getLocationOfResource($package,$resourcename);
                // file can always have been removed after adding it as a published resource
                if(file_exists("custom/packages/".$location)){
                    $classname = $this->getClassnameOfResource($package,$resourcename);
                    $doc->$package->$resourcename = new StdClass();
                    include_once("custom/packages/" . $location);
                    $doc->$package->$resourcename->doc = $classname::getDoc();
                    $doc->$package->$resourcename->requiredparameters = $classname::getRequiredParameters();
                    $doc->$package->$resourcename->parameters = $classname::getParameters();   
                }
            }
        }
        return $doc;
    }

    public function makeDescriptionDoc($doc){
        $this->makeDoc($doc);
    }

    private function getCreationTime($package, $resource) {
        //if the object read is a directory and the configuration methods file exists, 
        //then add it to the installed packages
        $location = $this->getLocationofResource($package,$resource);
        if (file_exists("custom/packages/" . $location)) {
            return filemtime("custom/packages/" . $location);
        }
        return 0;
    }
    
    private function getModificationTime($package, $resource) {
        // for an existing folder you can only get the last modification date in php, so 
        return $this->getCreationTime($package, $resource);
    }

    protected function getAllResourceNames(){
        /**
         * Get all the physical locations of published installed resources
         */
        $resources = array();
        $installedResources = DBQueries::getAllInstalledResources();
        foreach($installedResources as $installedResource){
            if(!array_key_exists($installedResource["package"],$resources)){
                $resources[$installedResource["package"]] = array();
            }
            $resources[$installedResource["package"]][] = $installedResource["resource"];
        }
        return $resources;
    }

    private function getLocationOfResource($package,$resource){
        return DBQueries::getLocationofResource($package,$resource);
    }

    private function getClassnameOfResource($package,$resource){
        return DBQueries::getClassnameOfResource($package,$resource);
    }
    

    /**
     * Put together the deletion documentation for installed resources
     */
    public function makeDeleteDoc($doc){
        $d = new StdClass();
        $d->doc = "Installed resources can be deleted from its location, yet it's physical classfile will remain in the folderstructure of the custom/packages folder.";
        if(!isset($doc->delete)){
            $doc->delete = new StdClass();
        }
        $doc->delete->installed = new StdClass();
        $doc->delete->installed = $d;
    }

    /**
     * Put together the creation documentation for installed resources
     */
    public function makeCreateDoc($doc){

        $d = new StdClass();
        $installedResource = new InstalledResourceCreator("","",array());
        $d->doc = "You can PUT an installed resource when you have created a resource-class in the custom/packages folder.";
        $d->parameters = $installedResource->documentParameters();
        $d->requiredparameters = $installedResource->documentRequiredParameters();

        if(!isset($doc->create)){
            $doc->create = new stdClass();
        }
        $doc->create->installed = new stdClass();
        $doc->create->installed = $d;
    }
}

?>
