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

//TODO

/**
 * This class is a method which returns all available modules for this DataTank.
 */
class Modules extends AResource{

    private $mod;

    public static function getParameters(){
	return array("mod" => "if you want only one module specify it here");
    }

    public static function getRequiredParameters(){
	return array();
    }

    public function setParameter($key,$val){
	if($key == "proxy" && $val == "1"){
	    $this->proxy = true;
	}else if($key == "mod"){
	    $this->mod = $val;
	}
    }

    public function call(){
	$f = AllResourceFactory::getInstance();
	$o = $f->getAllDocs();
	
	foreach($o->module as $module){
	    if($module->name == $this->mod){
		$o = $module;
		break;
	    }
	}
	return $o;
    }
     
    public static function getAllowedPrintMethods(){
	return array("json","xml", "jsonp", "php");
    }

    public static function getDoc(){
	return "This is a function which will return all supported modules by this API";
    }
}

?>
