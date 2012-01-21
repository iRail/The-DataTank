<?php
/**
 * This class will handle all resources installed in de package directory
 *
 * @package The-Datatank/model
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert
 *
 */

class InstalledResourceFactory extends AResourceFactory{
    
    public function createCreator($package,$resource, $parameters, $RESTparameters){
        //does nothing
    }
    
    public function createReader($package,$resource, $parameters, $RESTparameters){
        include_once("custom/packages/" . $package . "/" . $resource . ".class.php");
        $classname = $package . $resource;
        $creator = new $classname($package,$resource, $RESTparameters);
        $creator->processParameters($parameters);
        return $creator;
    }
    
    public function createDeleter($package,$resource, $RESTparameters){
        //does nothing
    }

    public function makeDoc($doc){
        //ask every resource we have for documentation
        foreach($this->getAllResourceNames() as $package => $resourcenames){
            if(!isset($doc->$package)){
                $doc->$package = new StdClass();
                //This has to move to another resource
                //$doc->$package->creation_date = filemtime("custom/packages/".$package);
            }
            foreach($resourcenames as $resourcename){
                $classname = $package . $resourcename;
                $doc->$package->$resourcename = new StdClass();
                include_once("custom/packages/" . $package . "/" . $resourcename . ".class.php");
                $doc->$package->$resourcename->doc = $classname::getDoc();
                $doc->$package->$resourcename->requiredparameters = $classname::getRequiredParameters();
		$doc->$package->$resourcename->parameters = $classname::getParameters();
                //Move this to another resource!
//                $doc->$package->$resourcename->creation_timestamp = $this->getCreationTime($package,$resourcename);
//                $doc->$package->$resourcename->modification_timestamp = $this->getModificationTime($package,$resourcename);
            }
        }
    }

    private function getCreationTime($package, $resource) {
        //if the object read is a directory and the configuration methods file exists, 
        //then add it to the installed packages
        if (is_dir("custom/packages/" . $package) && file_exists("custom/packages/" . $package . "/" . $resource . ".class.php")) {
            return filemtime("custom/packages/" . $package . "/" . $resource . ".class.php");
        }
        return 0;
    }
    
    private function getModificationTime($package, $resource) {
        // for an existing folder you can only get the last modification date in php, so 
        return $this->getCreationTime($package, $resource);
    }

    protected function getAllResourceNames(){
        $packages = array();
        //open the custom directory and loop through it
        if ($handle = opendir('custom/packages')) {
            while (false !== ($pack = readdir($handle))) {
                //if the object read is a directory and the configuration methods file exists, then add it to the installed packages
                if ($pack != "." && $pack != ".." && is_dir("custom/packages/" . $pack) && file_exists("custom/packages/" . $pack ."/resources.php")) {
                    include_once("custom/packages/" . $pack . "/resources.php");
                    $packages[$pack] = $pack::$resources;
                }
            }
            closedir($handle);
        }
        return $packages;
    }

    public function makeDeleteDoc($doc){
        //We cannot delete an Installed Resources
    }
    
    public function makeCreateDoc($doc){
        //we cannot create an Installed Resources on the fly
    }
}

?>
