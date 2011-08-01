<?php
/**
 * This will get a resource description from the databank and add the right strategie to process the call to the GenericResource class
 *
 * @package The-Datatank/factories
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert
 */

class GenericResourceFactory extends AResourceFactory{

    public function __construct($module,$resource){
	parent::__construct($module,$resource);
    }    

    /**
     * @return an array containing all the resourcenames available
     */
    public static function getAllResourceNames(){
	//TODO: logic for getting everything out of the DB
	return array();
    }

    public function hasResource(){
	return false; //TODO
    }
    

    /**
     * @return gets an instance of a AResource class.
     */
    public function getResource(){
	
    }
    
}

?>
