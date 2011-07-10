<?php
/* Copyright (C) 2011 by iRail vzw/asbl
 *
 * Author: Pieter Colpaert <pieter aŧ iRail.be>
 * License: AGPLv3
 *
 * This method is a proxy for methods on other servers
 * I think we'd better not do this in another module class...
 */

include_once("modules/AMethod.php");
include_once("modules/FederatedModules.php");

class Federated extends AMethod{

     private $module, $method, $url;
     private $arguments;

     public function __construct($module = "", $method = "", $url = ""){
	  if($module == ""){
	       throw new NotAMethodTDTException();
	  }
	  parent::__construct("Federated");
	  $this->module = $module;
	  $this->method = $method;
	  $this->url = $url;
     }

     public static function getParameters(){
	  //Allow all parameters
	  return array("*");
     }

     public static function getRequiredParameters(){
	  //No required Parameters: parameters will be checked by acceptingthing
	  return array();
     }

     public function setParameter($key,$val){
	  $this->arguments["key"] = $val;
     }

     public function call(){
	  $args = "?";
	  if(sizeof($this->arguments) > 0){
	       $i = 0;
	       foreach($this->arguments as $key => $val){
		    $args .= $key . "=" . $val . "&";
	       }
	  }
//	  echo file_get_contents($this->url . $this->method . "/" . $args . "format=php");
	  return unserialize(file_get_contents($this->url . $this->method . "/" . $args . "format=php"));
     }
     
     public function allowedPrintMethods(){
	  return array("xml", "json", "php", "jsonp");
     }

     public static function getDoc(){
	  $doc = "This class is a proxy. It will forward the call to another server. Only federated modules that are defined in modules/FederatedModules work.\n<br/>";
	  //include all federated modules
	  $doc .= " Available modules: ";
	  foreach(FederatedModules::$modules as $module => $url){
	       $doc .= "<a href=\"$url\">$module</a> ";
	  }
	  return $doc;
     }
}
?>