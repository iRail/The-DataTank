<?php
/**
 * This is a class which will return all the available resources in this DataTank
 * 
 * @package The-Datatank/packages/TDTInfo
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert   <pieter@iRail.be>
 * @author Jan Vansteenlandt <jan@iRail.be>
 */

class Resources extends AResource{

    public static function getParameters(){
	return array();
    }

    public static function getRequiredParameters(){
	return array();
    }

    public function setParameter($key,$val){
    }

    public function call(){
	$resmod = ResourcesModel::getInstance();
	$o = $resmod->getAllDocs();
	return $o;
    }
     
    public static function getAllowedPrintMethods(){
	return array("json","xml", "jsonp", "php", "html");
    }

    public static function getDoc(){
	return "This resource contains the most important information";
    }
}

?>
