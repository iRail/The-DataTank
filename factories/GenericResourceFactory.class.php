<?php
  /**
   * This will get a resource description from the databank and add the right strategy to process the call to the GenericResource class
   *
   * @package The-Datatank/factories
   * @copyright (C) 2011 by iRail vzw/asbl
   * @license AGPLv3
   * @author Pieter Colpaert
   * @author Jan Vansteenlandt
   */

include_once("resources/GenericResource.class.php");

class GenericResourceFactory extends AResourceFactory{

    private static $factory;
    
    private function __construct(){
    }
    
    public static function getInstance(){
	if(!isset(self::$factory)){
	    self::$factory = new GenericResourceFactory();
	}
	return self::$factory;
    }


    /**
     * @return returns a string containing the documentation about the resource. It returns an empty string when the resource could not be found
     */
    public function getResourceDoc($module, $resource){
	
	R::setup(Config::$DB,Config::$DB_USER,Config::$DB_PASSWORD);
	$param = array(':module' => $module, ':resource' => $resource);
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
    

    /**
     * @return returns an associative array with the documentation for each parameter for a specific resource 
     */
    public function getResourceParameters($module, $resource){
        // generic resources don't have parameters that can be passed along with the RESTful call
        return array();
    }
    

    /**
     * @return returns an array with all required parameters
     */
    public function getResourceRequiredParameters($module,$resource){
        // same remark as with getResourceParameters().
        return array();
    }
    
    public function getAllowedPrintMethods($module,$resource){

	R::setup(Config::$DB,Config::$DB_USER,Config::$DB_PASSWORD);
	$param = array(':module' => $module, ':resource' => $resource);
	$results = R::getAll(
	    "select generic_resource.print_methods as print_methods from module,generic_resource 
             where module.module_name=:module and generic_resource.resource_name =:resource 
             and module.id=generic_resource.module_id",
	    $param
	);
	$print_methods = explode(";", $results[0]["print_methods"]);

	return $print_methods;
    }    

    /**
     * @return a hash in which an array of resources is mapped on its module 
     */
    public function getAllResourceNames(){

	R::setup(Config::$DB,Config::$DB_USER,Config::$DB_PASSWORD);
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

    public function hasResource($module,$resource){

	R::setup(Config::$DB,Config::$DB_USER,Config::$DB_PASSWORD);
	$param = array(':module' => $module, ':resource' => $resource);
	$resource = R::getAll(
	    "select count(1) as present from module,generic_resource 
             where module.module_name=:module and generic_resource.resource_name=:resource
             and generic_resource.module_id=module.id",
	    $param
	);
	
	if(isset($resource[0]["present"]) && $resource[0]["present"] == 1){
	    return true;
	}
	return false;
    }    

    /**
     * @return gets an instance of a AResource class.
     */
    public function getResource($module,$resource){
	return new GenericResource($module,$resource);	
    }
    
  }

?>
