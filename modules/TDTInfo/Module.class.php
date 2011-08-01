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

     private $mod,$meth;

     public static function getParameters(){
	  return array("module" => "Module name","resource" => "This is the name of the resource");
     }

     public static function getRequiredParameters(){
	  return array("module","resource");
     }

     public function setParameter($key,$val){
	  if($key == "mod"){
	       $this->mod = $val;
	  }else if($key == "meth"){
	       $this->meth = $val;
	  }else if($key == "hostname"){
	       $this->hostname = $val;
	  }
     }

     public function call(){
	  $o = new stdClass();
	  if(in_array($this->mod,ProxyModules::getAll())){
	       //If we are only a proxy, return the jsonthing
	       return unserialize(TDT::HttpRequest(Config::HOSTNAME . "TDTInfo/Module/". $this->mod ."/" . $this->meth . "/?format=php")->data);
	  }else{
	       include_once("modules/" . $this->mod . "/" . $this->meth.".class.php");
	       $meth = $this->meth;
	       $o->doc = $meth::getDoc();
	       $o->url = Config::$HOSTNAME . Config::$SUBDIR;
	       $o->parameter =  $meth::getParameters();
	       $o->requiredparameter = $meth::getRequiredParameters();
	  }
	  return $o;
     }

     public static function getAllowedPrintMethods(){
	  return array("json","xml", "jsonp", "php");
     }

     public static function getDoc(){
	  return "This function will get all information about a specific method and return it";
     }
}

?>