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


    public function __construct($module,$resource){
	parent::__construct($module,$resource);
    }

    /**
     * @return an array containing all the resourcenames available
     */
    public static function getAllResourceNames(){
	return self::$resources;
    }
    
    /**
     * @return gets an instance of a AResource class.
     */
    public function getResource(){
	return new RemoteResource($this->urls[$this->module], $this->module, $this->resource);
    }
    
}

?>
