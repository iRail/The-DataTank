<?php
/**
 * This is a class which will return all the available modules for this DataTank
 * 
 * @package The-Datatank/modules/TDTInfo
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert   <pieter@iRail.be>
 * @author Jan Vansteenlandt <jan@iRail.be>
 */

class Modules extends AResource{

    public static function getParameters(){
	return array();
    }

    public static function getRequiredParameters(){
	return array();
    }

    public function setParameter($key,$val){
    }

    public function call(){
	$resourcefactory = AllResourceFactory::getInstance();
	$o = $resourcefactory->getAllDocs();	
	return $o;
    }
     
    public static function getAllowedPrintMethods(){
	return array("json","xml", "jsonp", "php", "html");
    }

    public static function getDoc(){
	return "This is a function which will return all supported modules by this API";
    }
}

?>
