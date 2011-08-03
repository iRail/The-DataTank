<?php
/**
 * Will combine all other factories in 1 factory!
 *
 * @package The-Datatank/factories
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert
 */

class AllResourceFactory extends AResourceFactory{

    private static $factory;

    private $factories;//array

    private function __construct(){
	$this->factories = array(); //(ordening does matter here! Put the least expensive method on top)
	$this->factories[] = GenericResourceFactory::getInstance();
	$this->factories[] = InstalledResourceFactory::getInstance();
	$this->factories[] = RemoteResourceFactory::getInstance();
    }
    
    public static function getInstance(){
	if(!isset(self::$factory)){
	    self::$factory = new AllResourceFactory();
	}
	return self::$factory;
    }

    /**
     * @return returns a string containing the documentation about the resource. It returns an empty string when the resource could not be found
     */
    public function getResourceDoc($module, $resource){
	foreach($this->factories as $factory){
	    if($factory->hasResource($module,$resource)){
		return $factory->getResourceDoc($module,$resource);
	    }
	}
	//if not really any factory has the resource, throw an exception
	throw new MethodOrModuleNotFoundTDTException($module . "/" .$resource);
    }

    /**
     * @return returns an associative array with the documentation for each parameter for a specific resource 
     */
    public function getResourceParameters($module, $resource){
	foreach($this->factories as $factory){
	    if($factory->hasResource($module,$resource)){
		return $factory->getResourceParameters($module,$resource);
	    }
	}
	//if not really any factory has the resource, throw an exception
	throw new MethodOrModuleNotFoundTDTException($module . "/" .$resource);
    }

    /**
     * @return returns an array with all required parameters
     */
    public function getResourceRequiredParameters($module,$resource){
	foreach($this->factories as $factory){
	    if($factory->hasResource($module,$resource)){
		return $factory->getResourceRequiredParameters($module,$resource);
	    }
	}
	//if not really any factory has the resource, throw an exception
	throw new MethodOrModuleNotFoundTDTException($module . "/" .$resource);
    }
    
    public function getAllowedPrintMethods($module,$resource){
	foreach($this->factories as $factory){
	    if($factory->hasResource($module,$resource)){
		return $factory->getAllowedPrintMethods($module,$resource);
	    }
	}
	//if not really any factory has the resource, throw an exception
	throw new MethodOrModuleNotFoundTDTException($module . "/" .$resource);
    }

    /**
     * @return an array containing all the resourcenames available
     */
    public function getAllResourceNames(){
	$rn = array();
	foreach($this->factories as $factory){
	    foreach($factory->getAllResourceNames() as $module => $resourcenames){
		if(isset($rn[$module])){
		    $rn[$module] = array_merge($rn[$module],$resourcenames);
		}else{
		    $rn[$module] = $resourcenames;
		}	
	    }
	}
	return $rn;
    }


    public function hasResource($module,$resource){
	foreach($this->factories as $factory){
	    if($factory->hasResource($module,$resourcename)){
		return true;
	    }
	}
	return false;
    }    

    /**
     * @return gets an instance of a AResource class.
     */
    public function getResource($module,$resource){
	//find the one who has the resource!
	foreach($this->factories as $factory){
	    if($factory->hasResource($module,$resource)){
		return $factory->getResource($module,$resource);
	    }
	}
	throw new MethodOrModuleNotFoundTDTException($module . "/" .$resource);
    }
    
}

?>
