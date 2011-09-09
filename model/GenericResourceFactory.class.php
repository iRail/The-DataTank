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
	$param = array(':module' => $package, ':resource' => $resource);
	$result = R::getAll(
	    "select generic_resource.documentation as doc from module,generic_resource 
             where module.module_name=:module and generic_resource.resource_name =:resource
             and module.id=generic_resource.module_id",
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
	$param = array(':module' => $package, ':resource' => $resource);
	$results = R::getAll(
	    "select generic_resource.print_methods as print_methods from module,generic_resource 
             where module.module_name=:module and generic_resource.resource_name =:resource 
             and module.id=generic_resource.module_id",
	    $param
	);
	$print_methods = explode(";", $results[0]["print_methods"]);

	return $print_methods;
    }    

    public function getAllResourceNames(){


	$results = R::getAll(
            "select generic_resource.resource_name as resource, module.module_name as module
             from module,generic_resource where generic_resource.module_id=module.id"
	);
	$resources = array();
	
	foreach($results as $result){
	    if(!array_key_exists($result["module"],$resources)){
		$resources[$result["module"]] = array();
	    }
	    array_push($resources[$result["module"]],$result["resource"]);
	}
	return $resources;
    }

    public function hasResource($package,$resource){
	$param = array(':module' => $package, ':resource' => $resource);
        
	$resource = R::getAll(
	    "select count(1) as present from module,generic_resource 
             where module.module_name=:module and generic_resource.resource_name=:resource
             and generic_resource.module_id=module.id",
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

            //now the only thing left to delete is the main row
            $deleteGenericResource = R::exec(
                "DELETE FROM generic_resource 
                          WHERE resource_name=:resource and module_id IN 
                            (SELECT id FROM module WHERE module_name=:module)",
                array(":module" => $package, ":resource" => $resource)
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
        $genres = R::dispense("generic_resource");
        $genres->module_id = $package_id;
        $genres->resource_name = $resource;
        $genres->type = $content["generic_type"];
        $genres->documentation = $content["documentation"];
        $genres->print_methods =  $content["printmethods"];
        $genres->timestamp = time();
        return R::store($genres);
    }

    /**
     * If the package/resource exists, then update the resource with the content provided
     */
    public function updateResource($package,$resource,$content){
        //TODO
        /*
         * Don't add relations between non-existing modules/resources !!
         */
        $original_id_query = R::getAll(
            "select gen_res_db.id from 
             module, generic_resource as gen_res, generic_resource_db as gen_res_db
             where module.module_name=:module_name and gen_res.module_id=module.id and gen_res.resource_name=:resource_name
            and gen_res.id=gen_res_db.resource_id",
            array(":module_name" => $module, ":resource_name" => $resource)
        );

        $original_id = $original_id_query[0]["id"];
        
        /*
         * Get the FK relation
         */
        $fk_module = $put_vars["foreign_module"];
        $fk_resource = $put_vars["foreign_resource"];
        $original_column_name = $put_vars["original_column_name"];
        $fk_id_query = R::getAll("select gen_res_db.id from 
             module, generic_resource as gen_res, generic_resource_db as gen_res_db
             where module.module_name=:module_name and gen_res.module_id=module.id and gen_res.resource_name=:resource_name
             and gen_res.id=gen_res_db.resource_id",
            array(":module_name" => $fk_module, ":resource_name" => $fk_resource)
        );
       $fk_id = $fk_id_query[0]["id"];
        /*
         * Add the foreign relation to the back-end
         */
        $db_foreign_relation = R::dispense("db_foreign_relation");
        $db_foreign_relation->main_object_id = $original_id;
        $db_foreign_relation->foreign_object_id = $fk_id;
       $db_foreign_relation->main_object_column_name = $original_column_name;
    return R::store($db_foreign_relation);
    }
}

?>
