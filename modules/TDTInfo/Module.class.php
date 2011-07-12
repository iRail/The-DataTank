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
include_once("modules/FederatedModules.php");
class Module extends AMethod{

     public function __construct(){
	  parent::__construct("Module");
     }

     public static function getParameters(){
	  return array("meth" => "Get information about one specific method");
     }

     public static function getRequiredParameters(){
	  return array("Meth");
     }

     public function setParameter($key,$val){
	  if($key == "federated" && $val == "0"){
	       $this->federated = false;
	  }
     }

     public function call(){
	  $o = new Object();
	  //TODO
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