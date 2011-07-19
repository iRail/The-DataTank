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
include_once("modules/ProxyModules.php");
include_once("TDT.class.php");
class Modules extends AMethod{

     private $mod;
     private $proxy = false;

     public function __construct(){
	  parent::__construct("Modules");
     }

     public static function getParameters(){
	  return array("mod" => "if you want only one module specify it here", "proxy" =>"this is a boolean: 1 or 0 - if the boolean is true (default), the proxy methods will be inluded in the call. When false, only native functions will be included.");
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
	  $o = new Object();
	  $modules = array();
	  $i=0;
	  if($this->proxy){
	       $proxymodules = ProxyModules::getAll();
	       foreach($proxymodules as $mod => $url){
		    $options = array("timeout" => 2);
		    //TODO - neat solution for this
		    $arr = str_replace("http://", "", $url);
		    $arr = str_replace("https://", "", $arr);
		    
		    $arr = explode("/",$arr);
		    //echo "http://" . $arr[0] . "/TDTInfo/Modules/?format=json&mod=". $arr[1];
		    
		    $resp = TDT::HttpRequest("http://" . $arr[0] . "/TDTInfo/Modules/?format=json&mod=". $arr[1], $options); 
		    if(!isset($resp->error)){
			 $module = json_decode($resp->data);
			 if(is_object($module)){
			      $modules[$i] = $module;
			      //alter the name of the module to our name in the system
			      $modules[$i]->name = $mod;
			      $modules[$i]->url = "http://" . $arr[0] . "/";
			      $i++;
			 }
		    }else{
			 echo "ERROR";
			 
			 //Put the URL in quarantaine and poll it from time to time until it comes up again. Then we can add it back to the list.
			 //TODO
		    }
	       }
	  }

	  $mods = InstalledModules::getAll();
	  
	  $modindex = -1;
	  foreach($mods as $mod){
	       //Now that we have all modules, let's search for their methods
	       include_once("modules/$mod/methods.php");
	       $modules[$i] = new Object();
	       foreach($mod::$methods as $method){
		    include_once("modules/$mod/$method.class.php");
		    if(isset($this->mod) && $mod == $this->mod){
			 $modindex=$i;
		    }
		    
		    $mm = new stdClass();
		    $mm->name = $method;
		    $mm->doc = $method::getDoc();
		    $mm->requiredparameters = $method::getRequiredParameters();
		    $mm->parameters = $method::getParameters();
		    $mm->format = $method::getAllowedPrintMethods();
		    $modules[$i]->method[] = $mm;
	       }
	       $modules[$i]->name = $mod;
	       $modules[$i]->url = Config::$HOSTNAME;
	       $i++;
	  }
	  $o->module = $modules;
	  //check if our modindex has changed, if not, return everything
	  if($modindex == -1){
	       return $o;
	  }else{
	       //otherwise, we will just return this module
	       return $o->module[$modindex];
	  }	  
     }
     
     public function allowedPrintMethods(){
	  return array("xml","json","php","jsonp");
     }
     
     public static function getAllowedPrintMethods(){
	  return array("json","xml", "jsonp", "php");
     }

     public static function getDoc(){
	  return "This is a function which will return all supported modules by this API";
     }
}

?>