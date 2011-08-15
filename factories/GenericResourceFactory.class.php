<?php
  /**
   * This will get a resource description from the databank and add the right strategy to process the call to the GenericResource class
   *
   * @package The-Datatank/factories
   * @copyright (C) 2011 by iRail vzw/asbl
   * @license AGPLv3
   * @author Pieter Colpaert
   */

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
	$queryTable = "generic_resource_param";
	$param = array(':module' => $module, ':resource' => $resource);
	$result = R::getAll(
	    "select resource_doc as doc from $queryTable where module=:module and resource =:resource",
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
	R::setup(Config::$DB,Config::$DB_USER,Config::$DB_PASSWORD);
	$queryTable = "generic_resource_doc";
	$param = array(':module' => $module, ':resource' => $resource);
	$results = R::getAll(
	    "select param,doc from $queryTable where module=:module and resource =:resource",
	    $param
	);

	$paramdocs = array();
	foreach($results as $result){
	    $paramdocs[$result["param"]] = $result["doc"];
	}
	return $paramdocs;
    }
    

    /**
     * @return returns an array with all required parameters
     */
    public function getResourceRequiredParameters($module,$resource){
	R::setup(Config::$DB,Config::$DB_USER,Config::$DB_PASSWORD);
	$queryTable = "generic_resource_doc";
	$param = array(':module' => $module, ':resource' => $resource);
	$results = R::getAll(
	    "select param from $queryTable where module=:module and resource =:resource and is_param_req = 1",
	    $param
	);
	
	$reqparams = array();
	
	foreach($results as $result){
	    array_push($reqparams,$result["param"]);
	}

	return $reqparams;
    }
    
    public function getAllowedPrintMethods($module,$resource){
	//get allowed printers from db
	R::setup(Config::$DB,Config::$DB_USER,Config::$DB_PASSWORD);
	$queryTable = "generic_resource_param";
	$param = array(':module' => $module, ':resource' => $resource);
	$results = R::getAll(
	    "select print_methods from $queryTable where module=:module and resource =:resource",
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
	$queryTable = "generic_resource_param";
	$results = R::getAll(
	    "select module,resource from $queryTable"
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
	$queryTable = "generic_resource_param";
	$param = array(':module' => $module, ':resource' => $resource);
	$resource = R::getAll(
	    "select count(1) as present from $queryTable where module=:module and resource=:resource",
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
	
    }
    
  }

?>
