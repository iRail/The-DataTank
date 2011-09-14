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
	$param = array(':package' => $package, ':resource' => $resource);
	$result = R::getAll(
	    "SELECT generic_resource.documentation as doc 
             FROM package,generic_resource,resource 
             WHERE package.package_name=:package and resource.resource_name =:resource
             and package.id=resource.package_id and resource.id = generic_resource.resource_id",
	    $param
	);
	
	$doc = "";
	if(isset($result[0]["doc"])){
	    $doc = $result[0]["doc"];
	}
	return $doc;
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
	R::setup(Config::$DB,Config::$DB_USER,Config::$DB_PASSWORD);
	$param = array(':package' => $package, ':resource' => $resource);
	$results = R::getAll(
	    "SELECT generic_resource.print_methods as print_methods 
             FROM package,generic_resource,resource 
             WHERE package.package_name=:package and resource.resource_name =:resource 
             and package.id=resource.package_id and resource.id = generic_resource.resource_id",
	    $param
	);
	$print_methods = explode(";", $results[0]["print_methods"]);

	return $print_methods;
    }    

    public function getAllResourceNames(){


	$results = R::getAll(
            "SELECT resource.resource_name as res_name, package.package_name
             FROM package,generic_resource,resource 
             WHERE resource.package_id=package.id and generic_resource.resource_id=resource.id"
	);
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
	$param = array(':package' => $package, ':resource' => $resource);
        
	$resource = R::getAll(
	    "SELECT count(1) as present 
             FROM package,generic_resource,resource 
             WHERE package.package_name=:package and resource.resource_name=:resource
             and resource.package_id=package.id and generic_resource.resource_id=resource.id",
	    $param
	);
        
	return isset($resource[0]["present"]) && $resource[0]["present"] == 1;   
    }
    
    public function getResource($package,$resource){
	return new GenericResource($package,$resource);	
    }

    /*************************************SETTERS*****************************************************/

    public function deleteResource($package,$resource){
        //first we need to check what kind of strategy we are dealing with and delete it according to the strategy
       
        
        if($this->hasResource($package, $resource)){
            $res = $this->getResource($package,$resource);
            $strategy = $res->getStrategy();
            $strategy->onDelete($package,$resource);

            $deleteForeignRelation = R::exec(
                        "DELETE FROM foreign_relation 
                                WHERE main_object_id IN 
                                (SELECT gen_res.id 
                                 FROM  resource, package, generic_resource as gen_res
                                 WHERE package_name=:package and package.id=package_id and resource.resource_name=:resource 
                                       and gen_res.resource_id = resource.id) 
                                 OR 
                                 foreign_object_id IN 
                                (SELECT gen_res.id 
                                 FROM resource, package, generic_resource as gen_res
                                 WHERE package_name=:package and package.id=package_id and resource.resource_name=:resource 
                                       and gen_res.resource_id = resource.id
                                 )",
                        array(":package" => $package, ":resource" => $resource)
            );
            
            //now the only thing left to delete is the main row
            $deleteGenericResource = R::exec(
                "DELETE FROM generic_resource
                          WHERE resource_id IN 
                            (SELECT resource.id 
                             FROM package,resource
                             WHERE package_name=:package and resource.id = generic_resource.resource_id
                             and resource.resource_name =:resource and resource.package_id = package.id  
                             )",
                array(":package" => $package, ":resource" => $resource)
            );
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
        $package_id = $model->makePackageId($package);

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
        $resource_id = $model->getResourceId($package_id,$resource);
        
        $genres = R::dispense("generic_resource");
        $genres->resource_id = $resource_id;
        $genres->type = $content["generic_type"];
        $genres->documentation = $content["documentation"];
        $genres->print_methods =  $content["printmethods"];
        $genres->timestamp = time();
        return R::store($genres);
    }
}

?>
