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
    
    public function createCreator($package,$resource, $parameters){
        //does nothing
    }
    
    public function createReader($package,$resource, $parameters){
        include_once("custom/packages/" . $package . "/" . $resource . ".class.php");
        $creator = new $resource($package,$resource);
        $creator->processParameters($parameters);
        return $creator;
    }
    
    public function createDeleter($package,$resource){
        //does nothing
    }

    public function makeDoc($doc){
        //ask every resource we have for documentation
        foreach($this->getAllResourceNames() as $package => $resourcenames){
            if(!isset($doc->$package)){
                $doc->$package = new StdClass();
            }
            foreach($resourcenames as $resourcename){
                $doc->$package->$resourcename = new StdClass();
                include_once("custom/packages/" . $package . "/" . $resourcename . ".class.php");
                $doc->$package->$resourcename->doc = $resourcename::getDoc();
                $doc->$package->$resourcename->requiredparameters = $resourcename::getRequiredParameters();
		$doc->$package->$resourcename->parameters = $resourcename::getParameters();
                if(function_exists("$resourcename::getAllowedFormatters")){
                    $doc->$package->$resourcename->formats = $resourcename::getAllowedFormatters();
                }
                $doc->$package->$resourcename->creation_timestamp = $this->getCreationTime($package,$resourcename);
                $doc->$package->$resourcename->modification_timestamp = $this->getModificationTime($package,$resourcename);
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
}

?>
