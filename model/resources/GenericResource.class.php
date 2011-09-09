<?php
/**
 * The abstract class for a factory: check documentation on the Factory Method Pattern if you don't understand this code.
 *
 * @package The-Datatank/resources
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert
 */

include_once("model/resources/strategies/AResourceStrategy.class.php");
include_once("model/resources/AResource.class.php");

class GenericResource extends AResource {
    private $strategyname;
    private $strategy; //this contains the right strategy to handle the call
    
    private $package;
    private $resource;
    
    public function __construct($package,$resource){
        $this->package = $package;
        $this->resource = $resource;

	R::setup(Config::$DB,Config::$DB_USER,Config::$DB_PASSWORD);
	$param = array(':package' => $this->package, ':resource' => $this->resource);
	$result = R::getAll(
	    "select generic_resource.type as type from package,generic_resource
             where package.package_name=:package and generic_resource.resource_name=:resource
             and package.id=generic_resource.package_id",
	    $param
	);
	
	$this->strategyname = $result[0]["type"];
    }

    public function getStrategy(){
        if(is_null($this->strategy)){
            include_once("model/resources/strategies/" . $this->strategyname . ".class.php");
            $this->strategy = new $this->strategyname();
        }
        return $this->strategy;
    }
    

    public function call(){
        $strat = $this->getStrategy();
        return $strat->onCall($this->package,$this->resource);
    }

    public function setParameter($name,$val){
	$this->strategy->$name = $val;
    }

    public function getAllowedPrintMethods(){
	R::setup(Config::$DB,Config::$DB_USER,Config::$DB_PASSWORD);
	$param = array(':package' => $this->package, ':resource' => $this->resource);
	$results = R::getAll(
	    "select generic_resource.print_methods as print_methods from package,generic_resource
             where package.package_name=:package and generic_resource.resource_name =:resource 
             and package.id=generic_resource.package_id",
	    $param
	);
	$print_methods = explode(";", $results[0]["print_methods"]);

	return $print_methods;
    }
    
}

?>