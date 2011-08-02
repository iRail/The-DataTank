<?php
/**
 * The abstract class for a factory: check documentation on the Factory Method Pattern if you don't understand this code.
 *
 * @package The-Datatank/factories
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert
 */

abstract class AResourceFactory{
    
    protected $module, $resource;

    public function __construct($module,$resource){
	$this->module = $module;
	$this->resource = $resource;	
    }


    /**
     * @return returns whether the Factory can return a resource
     */
    public function hasResource(){
	$rn = self::getAllResourceNames();
	return in_array($this->resource, $rn[$this->module]);
    }
         
    /**
     * 
     * @return an array containing all the resourcenames available
     */
    static function getAllResourceNames() {
        return array();
    }
    
    
    /**
     * @return gets an instance of a AResource class.
     */
    abstract function getResource();
    
}

?>
