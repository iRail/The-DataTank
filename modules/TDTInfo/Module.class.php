<?php
/* Copyright (C) 2011 by iRail vzw/asbl
 *
 * Author: Pieter Colpaert <pieter aŧ iRail.be>
 * License: AGPLv3
 *
 * For a given module and method this will return the needed information
 */

include_once("modules/AMethod.php");
include_once("modules/InstalledModules.php");
include_once("modules/ProxyModules.php");
include_once("Config.class.php");
class Module extends AMethod{

     private $mod,$meth;

     public function __construct(){
	  parent::__construct("Module");
     }

     public static function getParameters(){
	  return array("mod" => "Module name","meth" => "Method name");
     }

     public static function getRequiredParameters(){
	  return array("mod","meth");
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
	       //If we are only a proxy, refer the jsonthing
	       return json_decode(TDT::HttpRequest(Config::HOSTNAME . "TDTInfo/Module/?format=json&meth=" . $this->meth . "&mod=" . $this->mod));
	  }else{
	       include_once("modules/" . $this->mod . "/" . $this->meth.".class.php");
	       $meth = $this->meth;
	       $o->doc = $meth::getDoc();
	       $o->parameter =  $meth::getParameters();
	       $o->requiredparameter = $meth::getRequiredParameters();
	  }
	  return $o;
     }
     
     public function allowedPrintMethods(){
	  return array("xml","json","php","jsonp");
     }

     public static function getDoc(){
	  return "This function will get all information about a specific method and return it";
     }
}

?>