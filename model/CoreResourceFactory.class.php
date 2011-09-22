<?php
/**
 * This class will handle all resources needed by the core. For instance the resources provided by the TDTInfo package.
 *
 * @package The-Datatank/model
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert
 */

class CoreResourceFactory extends AResourceFactory {

    public function __construct(){
        
    }

    public function createCreator($package,$resource, $parameters){
        
    }
    
    public function createReader($package,$resource, $parameters){
        
    }
    
    public function createUpdater($package,$resource, $parameters){
        
    }
    
    public function createDeleter($package,$resource){
        
    }

    public function makeDoc($doc){
        //ask every resource we have for documentation
        foreach($this->getAllResourceNames() as $package => $resourcenames){
            foreach($resourcenames as $resourcename){
                include_once("model/packages/" . $package . "/" . $resourcename . ".class.php");
                $docs->$package->$resourcename->doc = $resourcename::getDoc();
                $docs->$package->$resourcename->requiredparameters = $resourcename::getRequiredParameters();
		$docs->$package->$resourcename->parameters = $resourcename::getParameters();
		$docs->$package->$resourcename->formats = $resourcename::getAllowedFormats();
                $docs->$package->$resourcename->creation_timestamp = $this->getCreationTime($package,$resource);
                $docs->$package->$resourcename->modification_timestamp = $this->getModificationTime($package,$resource);
            }
        }
    }

    private function getCreationTime($package, $resource) {
        //if the object read is a directory and the configuration methods file exists, 
        //then add it to the installed packages
        if (is_dir("model/packages/" . $package) && file_exists("model/packages/" . $package . "/" . $resource . ".class.php")) {
            return filemtime("model/packages/" . $package . "/" . $resource . ".class.php");
        }
        return 0;
    }
    
    private function getModificationTime($package, $resource) {
        // for an existing folder you can only get the last modification date in php, so 
        return $this->getCreationTime($package, $resource);
    }

    private function getAllResourceNames(){
        return array("TDTInfo" => array("Resources", "Queries", "Packages", "Exceptions", "Mapping"));
    }
    
}

?>
