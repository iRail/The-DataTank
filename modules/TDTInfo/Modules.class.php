<?php
/* Copyright (C) 2011 by iRail vzw/asbl
 *
 * Author: Pieter Colpaert <pieter aŧ iRail.be>
 * License: AGPLv3
 *
 * This is a class which will return all the available modules for this DataTank
 */

include_once("modules/AMethod.php");
include_once("modules/InstalledModules.php");
include_once("modules/FederatedModules.php");
class Modules extends AMethod{

     private $federated = true;

     public function __construct(){
	  parent::__construct("Modules");
     }

     public static function getParameters(){
	  return array("federated" =>"this is a boolean: 1 or 0 - if the boolean is true (default), the federated methods will be inluded in the call. When false, only native functions will be included.");
     }

     public static function getRequiredParameters(){
	  return array();
     }

     public function setParameter($key,$val){
	  if($key == "federated" && $val == "0"){
	       $this->federated = false;
	  }
     }

     public function call(){
	  $o = new Object();
	  $modules = array();
	  
	  if($this->federated){
	       $federatedmodules = FederatedModules::getAll();
	       //TODO - Do a call to the federated module: server/TDTInfo/Module/...
	  }

	  $mods = InstalledModules::getAll();
	  $i=0;
	  foreach($mods as $mod){
	       //Now that we have all modules, let's search for their methods
	       include_once("modules/$mod/methods.php");
	       $modules[$i] = new Object();
	       foreach($mod::$methods as $method){		    
		    include_once("modules/$mod/$method.class.php");
		    $mm = new Object();
		    $mm->name = $method;
		    $mm->doc = $method::getDoc();
		    $modules[$i]->method[] = $mm;
	       }
	       $modules[$i]->name = $mod;
	       $i++;
	  }
	  $o->module = $modules;
	  return $o;
     }
     
     public function allowedPrintMethods(){
	  return array("xml","json","php","jsonp");
     }

     public static function getDoc(){
	  return "This is a function which will return all supported modules by this API";
     }
}

?>