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

     public function getParameters(){
	  return array("module" => "Module name","resource" => "This is the name of the resource");
     }

     public function getRequiredParameters(){
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
	  if(in_array($this->module,RemoteResourceFactory::getAllResourceNames())){
	       //If we are only a proxy, return the jsonthing
	       return unserialize(TDT::HttpRequest(Config::HOSTNAME . "TDTInfo/Module/". $this->mod ."/" . $this->meth . "/?format=php")->data);
	  }else{
	       include_once("modules/" . $this->module . "/" . $this->method.".class.php");
	       $resource = $this->resource;
	       //$o->doc = $meth::getDoc();
	       $o->url = Config::$HOSTNAME . Config::$SUBDIR;
	       //$o->parameter =  $meth::getParameters();
	       //$o->requiredparameter = $meth::getRequiredParameters();
	  }
	  return $o;
     }

     public function getAllowedPrintMethods(){
	 return array("json","xml", "jsonp", "php", "html");
     }

     public function getDoc(){
	  return "This function will get all information about a specific method and return it";
     }
}

?>