<?php
/**
 * The abstract class for a factory: check documentation on the Factory Method Pattern if you don't understand this code.
 *
 * @package The-Datatank/resources
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert
 */

include_once("resources/strategies/AResourceStrategy.class.php");
include_once("resources/AResource.class.php");

class GenericResource extends AResource {
    
    private $strategy; //this contains the right strategy to handle the call
    private $module;
    private $resource;
    
    public function __construct($module,$resource){
        $this->module = $module;
        $this->resource = $resource;
	
	//parent::__construct($module,$resource); // pieter you can't use a constructor 
	// on an abstract class......?!!
	R::setup(Config::$DB,Config::$DB_USER,Config::$DB_PASSWORD);
	$queryTable = "generic_resource_param";
	$param = array(':module' => $this->module, ':resource' => $this->resource);
	$result = R::getAll(
	    "select type from $queryTable where module=:module and resource=:resource",
	    $param
	);
	
	$this->strategy = $result[0]["type"];
	include_once("resources/strategies/$this->strategy.class.php");
    }

    public function call(){
        R::setup(Config::$DB,Config::$DB_USER,Config::$DB_PASSWORD);
        $queryTable = "generic_resource_param";
        $param = array(':module' => $this->module, ':resource' => $this->resource);
        $result = R::getAll(
            "select call_params from $queryTable where module=:module and resource=:resource",
            $param
        );
        
        $parameters = explode("=",$result[0]["call_params"]);
        include_once("resources/strategies/" . $this->strategy. ".class.php");
        $this->strategy = new $this->strategy();
        $this->strategy->fillInGenericParameters($parameters[1],array());
        return $this->strategy->call();
    }

    public function setParameter($name,$val){
	$this->strategy->$name = $val;
    }

    public function getAllowedPrintMethods(){
	R::setup(Config::$DB,Config::$DB_USER,Config::$DB_PASSWORD);
	$queryTable = "generic_resource_param";
	$param = array(':module' => $this->module, ':resource' => $this->resource);
	$results = R::getAll(
	    "select print_methods from $queryTable where module=:module and resource =:resource",
	    $param
	);
	$print_methods = explode(";", $results[0]["print_methods"]);

	return $print_methods;
    }
    
}

?>