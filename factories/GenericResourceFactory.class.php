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
	//get doc from db
    }
    

    /**
     * @return returns an associative array with the documentation for each parameter for a specific resource 
     */
    public function getResourceParameters($module, $resource){
	//get resource params from db
    }
    

    /**
     * @return returns an array with all required parameters
     */
    public function getResourceRequiredParameters($module,$resource){
	//get required params from db
    }
    
    public function getAllowedPrintMethods($module,$resource){
	//get allowed printers from db
    }    

    /**
     * @return an array containing all the resourcenames available
     */
    public function getAllResourceNames(){
	//TODO: logic for getting everything out of the DB
	return array();
    }

    public function hasResource($module,$resource){
	return false; //TODO
    }    

    /**
     * @return gets an instance of a AResource class.
     */
    public function getResource($module,$resource){
	
    }
    
}

?>
