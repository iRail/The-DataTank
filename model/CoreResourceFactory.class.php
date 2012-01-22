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

    protected function getAllResourceNames(){
        return array("TDTInfo" => array("Resources","Packages", "Exceptions", "Ontology", "Admin", "Formatters"),
                     "TDTStats" => array("UserAgents","Month","Week","Day","Languages"));
    }

    public function createCreator($package,$resource, $parameters, $RESTparameters){
        //do nothing
    }
    
    public function createReader($package,$resource, $parameters, $RESTparameters){
        include_once("model/packages/" . $package . "/" . $resource . ".class.php");
        $classname  = $package . $resource;
        $creator = new $classname($package,$resource, $RESTparameters);
        $creator->processParameters($parameters);
        return $creator;
    }
    
    public function createDeleter($package,$resource, $RESTparameters){
        //do nothing
    }

    public function makeDoc($doc){
        //ask every resource we have for documentation
        foreach($this->getAllResourceNames() as $package => $resourcenames){
            if(!isset($doc->$package)){
                $doc->$package = new StdClass();
//                $doc->$package->creation_date = filemtime("model/packages/".$package);
            }
            foreach($resourcenames as $resourcename){
                $classname = $package . $resourcename;
                $doc->$package->$resourcename = new StdClass();
                include_once("model/packages/" . $package . "/" . $resourcename . ".class.php");                
                $doc->$package->$resourcename->doc = $classname::getDoc();
                $doc->$package->$resourcename->requiredparameters = $classname::getRequiredParameters();
		$doc->$package->$resourcename->parameters = $classname::getParameters();
//                $doc->$package->$resourcename->creation_timestamp = $this->getCreationTime($package,$resourcename);
//                $doc->$package->$resourcename->modification_timestamp = $this->getModificationTime($package,$resourcename);
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

    public function makeDeleteDoc($doc){
        //We cannot delete Core Resources
    }
    
    public function makeCreateDoc($doc){
        //we cannot create Core Resources
    }
    

}
?>
