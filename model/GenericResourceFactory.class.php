<?php
/**
 * This will get a resource description from the databank and add the right strategy to process the call to the GenericResource class
 *
 * @package The-Datatank/model
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert
 * @author Jan Vansteenlandt
 */

include_once("model/resources/AResource.class.php");

class GenericResourceFactory extends AResourceFactory {

    public function createCreator($package,$resource, $parameters){
        include_once("model/resources/create/GenericResourceCreator.class.php");
        if(!isset($parameters["generic_type"])){
            throw new ResourceAdditionTDTException("generic type hasn't been set");
        }
        $creator = new GenericResourceCreator($parameters["generic_type"]);
        $creator->processParameters($parameters);
        return $creator;
    }
    
    public function createReader($package,$resource, $parameters){
        include_once("model/resources/read/GenericResourceReader.class.php");
        $reader = new GenericResourceReader($package, $resource, $parameters["generic_type"]);
        $reader->processParameters($parameters);
        return $reader;
    }
        
    public function createDeleter($package,$resource){
        include_once("model/resources/delete/GenericResourceDeleter.class.php");
        $deleter = new GenericResourceDeleter($package,$resource);
        return $deleter;
    }

    public function makeDoc($doc){
        //ask every resource we have for documentation
        foreach($this->getAllResourceNames() as $package => $resourcenames){
            foreach($resourcenames as $resourcename){
                include_once("model/packages/" . $package . "/" . $resourcename . ".class.php");
                $docs->$package->$resourcename->doc = $resourcename::getDoc();
                $docs->$package->$resourcename->requiredparameters = $resourcename::getRequiredParameters();
                $docs->$package->$resourcename->parameters = $resourcename::getParameters();
                $docs->$package->$resourcename->creation_timestamp = $this->getCreationTime($package,$resource);
                $docs->$package->$resourcename->modification_timestamp = $this->getModificationTime($package,$resource);
            }
        }
    }

    protected function getAllResourceNames(){
        $results = DBQueries::getAllGenericResourceNames();
        $resources = array();
        foreach($results as $result){
            if(!array_key_exists($result["package_name"],$resources)){
        	    $resources[$result["package_name"]] = array();
            }
            $resources[$result["package_name"]][] = $result["res_name"];
        }
        return $resources;
    }

}

?>
