<?php
/**
 * This is a class which will return all the packages in The DataTank
 * 
 * @package The-Datatank/packages/TDTInfo
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@iRail.be>
 */

class Packages extends AResource{

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
	$o = $resmod->getAllPackages();
	return $o;
    }
     
    public static function getAllowedFormatters(){
	return array("json","xml", "jsonp", "php", "html");
    }

    public static function getDoc(){
	return "This resource contains every package installed on this DataTank instance.";
    }
}

?>
