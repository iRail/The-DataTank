<?php
/**
 * This class will handle all resources installed in de module directory
 *
 * @package The-Datatank/factories
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert
 */

class InstalledResourceFactory extends AResourceFactory{

    private static $factory;
    
    private function __construct(){
    }
    
    public static function getInstance(){
	if(!isset(self::$factory)){
	    self::$factory = new InstalledResourceFactory();
	}
	return self::$factory;
    }

    /**
     * This function loads a resource if not yet included. On fail throw an error.
     */
    private function includeResource($module,$resource){
	if($this->hasResource($module,$resource)){
	    include_once("modules/$module/$resource.class.php");
	}else{
	    throw new MethodOrModuleNotFoundTDTException($module,$resource);
	}
    }
    
	
    /**
     * @return returns a string containing the documentation about the resource. It returns an empty string when the resource could not be found
     */
    public function getResourceDoc($module, $resource){
	$this->includeResource($module,$resource);	
	return $resource::getDoc();
    }

    /**
     * @return returns an associative array with the documentation for each parameter for a specific resource 
     */
    public function getResourceParameters($module, $resource){
	$this->includeResource($module,$resource);
	return $resource::getParameters();	
    }

    /**
     * @return returns an array with all required parameters
     */
    public function getResourceRequiredParameters($module,$resource){
	$this->includeResource($module,$resource);
	return $resource::getRequiredParameters();
    }

    /**
     * @return a boolean if resource exists
     */
    public function hasResource($module,$resource){
	return file_exists("modules/" . $module . "/" . $resource . ".class.php");
    }
 
    public function getAllowedPrintMethods($module,$resource){
	$this->includeResource($module,$resource);
	return $resource::getAllowedPrintMethods();
    	
    }
    
    /**
     * Scans the folder modules for other resources
     * @return an array containing all the resourcenames available
     */
    public function getAllResourceNames(){
	$modules = array();
	//open the modules directory and loop through it
	if ($handle = opendir('modules/')) {
	    while (false !== ($modu = readdir($handle))) {
		//if the object read is a directory and the configuration methods file exists, then add it to the installed modules
		if ($modu != "." && $modu != ".." && is_dir("modules/" . $modu) && file_exists("modules/" . $modu ."/resources.php")) {
		    include_once("modules/" . $modu ."/resources.php");
		    $modules[$modu] = $modu::$resources;
		}
	    }
	    closedir($handle);
	}

	return $modules;
    }
    
    /**
     * @return gets an instance of a AResource class.
     */
    public function getResource($module, $resource){
	include_once("modules/" . $module . "/" . $resource . ".class.php");
	return new $resource($module,$resource);
    }
}

?>
