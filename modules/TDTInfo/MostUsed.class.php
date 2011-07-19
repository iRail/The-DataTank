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
class MostUsed extends AMethod{

     public function __construct(){
	  parent::__construct("MostUsed");
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
	       $modules =array_merge(InstalledModules::getAll(), array_keys(FederatedModules::getAll()));
	  }else{	       
	       $modules = InstalledModules::getAll();
	  }
	  //Now that we have all modules, let's search for their methods
	  foreach($modules as $m){
//	       $m = $;

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