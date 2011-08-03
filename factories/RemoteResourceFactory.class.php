<?php
/**
 * This class will handle a remote resource and connect to another DataTank instance for their data
 *
 * @package The-Datatank/factories
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert
 */

class RemoteResourceFactory extends AResourceFactory{

    public static $resources = array(
//	  "GF" =>  array("Events")// this is a testing datatank
	  );

    public static $urls = array(
	//"GF" => "http://jan.irail.be/GentseFeesten/"
    );

    private static $factory;

    private function __construct(){
    }
    
    public static function getInstance(){
	if(!isset(self::$factory)){
	    self::$factory = new RemoteResourceFactory();
	}
	return self::$factory;
    }

    /**
     * @return returns a string containing the documentation about the resource. It returns an empty string when the resource could not be found
     */
    public function getResourceDoc($module, $resource){
	//todo - get from remote
    }
    

    /**
     * @return returns an associative array with the documentation for each parameter for a specific resource 
     */
    public function getResourceParameters($module, $resource){
	//todo - get from remote
    }    

    /**
     * @return returns an array with all required parameters
     */
    public function getResourceRequiredParameters($module,$resource){
	//todo - get from remote
    }

    public function getAllowedPrintMethods($module,$resource){
	//todo - get from remote
    }
    

    public function hasResource($module,$resource){
	$rn = $this->getAllResourceNames();
	if(isset($rn[$module])){
	    return in_array($resource, $rn[$module]);
	}
	return false;
    }

    /**
     * @return an array containing all the resourcenames available
     */
    public function getAllResourceNames(){
	return self::$resources;
    }
    
    /**
     * @return gets an instance of a AResource class.
     */
    public function getResource($module,$resource){
	return new RemoteResource($this->urls[$module], $module, $resource);
    }
    
}

?>
