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

include_once("model/resources/GenericResource.class.php");

class GenericResourceFactory extends AResourceFactory{

    public function getResourceDoc($package, $resource){
        $result = DBQueries::getGenericResourceDoc($package, $resource);
        return isset($result["doc"])?$result["doc"]:"";
    }
    
    public function getResourceParameters($package, $resource){
        // generic resources don't have parameters that can be passed along with the RESTful call
        return array();
    }
    
    public function getResourceRequiredParameters($package,$resource){
        // same remark as with getResourceParameters().
        return array();
    }
    
    public function getAllowedPrintMethods($package,$resource){
        $result = DBQueries::getGenericResourcePrintMethods($package, $resource);
        return isset($result["print_methods"])?explode(";", $result["print_methods"]):array();
    }    

    public function getAllResourceNames(){
        $results = DBQueries::getAllGenericResourceNames();
        $resources = array();
        
        foreach($results as $result){
            if(!array_key_exists($result["package_name"],$resources)){
        	    $resources[$result["package_name"]] = array();
            }
            array_push($resources[$result["package_name"]],$result["res_name"]);
        }
        return $resources;
    }

    public function hasResource($package,$resource){
        $resource = DBQueries::hasGenericResource($package, $resource);
        return isset($resource["present"]) && $resource["present"] == 1;   
    }
    
    public function getResource($package,$resource){
        return new GenericResource($package,$resource);	
    }

    public function getCreationTime($package,$resource){
        return DBQueries::getCreationTime($package,$resource);    
     }
    
    public function getModificationTime($package,$resource){
        return DBQueries::getModificationTime($package,$resource);
    }
    

    /*************************************SETTERS*****************************************************/

    public function deleteResource($package,$resource){
        //first we need to check what kind of strategy we are dealing with and delete it according to the strategy
       
        if($this->hasResource($package, $resource)){
            $res = $this->getResource($package,$resource);
            $strategy = $res->getStrategy();
            $strategy->onDelete($package,$resource);
            // delete any foreign relation of which either the main or foreign id
            // relates to
            DBQueries::deleteForeignRelation($package,$resource);
            
            // delete any published columns entry
            DBQueries::deletePublishedColumns($package,$resource);
            
            //now the only thing left to delete is the main row
            DBQueries::deleteGenericResource($package, $resource);
        }
    }
 
    /**
     * delete all resources related to the package
     */
    public function deletePackage($package){
        //this will get /all/ resource names
        
        $resources = $this->getAllResourceNames();
        // you now have ALL the resources of the generic type
        // we now want the ones with $package as package name
        if(isset($resources[$package])){
            $resources = $resources[$package];
            //this will try to delete non-existing resources as well
            foreach($resources as $resource){
                $this->deleteResource($package,$resource);
            }
        }
    }

    /**
     * Add a resource to a (existing/non-existing) package
     */
    public function addResource($package,$resource, $content){
        
        if($this->hasResource($package,$resource)){
            throw new ResourceAdditionTDTException("package/resource already exists");
        }
        if(!isset($content["generic_type"])){
            throw new ParameterTDTException("generic_type");
        }
        if(!file_exists("model/resources/strategies/" . $content["generic_type"] . ".class.php")){
            throw new ResourceAdditionTDTException("Generic type does not exist");
        }
        $model = ResourcesModel::getInstance();
        $package_id = parent::makePackageId($package);

        //So when the resource doesn't exist yet, when the generic type is set and when the strategy exists, do
        $resource_id = $this->makeGenericResourceId($package_id,$resource,$content);

        $type = $content["generic_type"];
        include_once("model/resources/strategies/" . $type . ".class.php");
        $strategy = new $type();
        $strategy->onAdd($package_id,$resource_id,$content);
    }

    private function makeGenericResourceId($package_id,$resource,$content){
        //will return the id of the new generic resource
        $model = ResourcesModel::getInstance();
        $resource_id = parent::makeResourceId($package_id,$resource, "generic");
        return DBQueries::storeGenericResource($resource_id, $content["generic_type"], $content["documentation"], $content["printmethods"]);
    }
}

?>
