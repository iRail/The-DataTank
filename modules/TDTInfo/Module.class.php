<?php
/**
 * This file contains Module.class.php
 * @package The-Datatank/modules/TDTInfo
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert   <pieter@iRail.be>
 * @author Jan Vansteenlandt <jan@iRail.be>
 */

class Module extends AResource{

     private $module,$resource;

     public static function getParameters(){
	  return array("module" => "Module name","resource" => "This is the name of the resource");
     }

     public static function getRequiredParameters(){
	  return array("module","resource");
     }

     public function setParameter($key,$val){
	  if($key == "module"){
	       $this->module = $val;
	  }else if($key == "resource"){
	       $this->resource = $val;
	  }
     }

     public function call(){
	  $o = new stdClass();
	  return $o;
     }

     public static function getAllowedPrintMethods(){
	 return array("json","xml", "jsonp", "php", "html");
     }

     public static function getDoc(){
	  return "This function will get all information about a specific method and return it";
     }
}

?>