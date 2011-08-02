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
     private $proxy = false;

     public function getParameters(){
	  return array("mod" => "if you want only one module specify it here", "proxy" =>"this is a boolean: 1 or 0 - if the boolean is true (default), the proxy methods will be inluded in the call. When false, only native functions will be included.");
     }

     public function getRequiredParameters(){
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
	  $o = new stdClass();
	  $modules = array();
	  $i=0;
	  if($this->proxy){
	      $proxymodules = RemoteResourceFactory::getAllResourceNames();
	       foreach($proxymodules as $mod => $url){
		    $options = array("timeout" => 2);
		    //TODO - neat solution for this
		    $arr = str_replace("http://", "", $url);
		    $arr = str_replace("https://", "", $arr);
		    
		    $arr = explode("/",$arr);
		    //echo "http://" . $arr[0] . "/TDTInfo/Modules/?format=json&mod=". $arr[1];
		    
		    $resp = TDT::HttpRequest("http://" . $arr[0] . "/TDTInfo/Modules/" . $arr[1] . "?format=json", $options); 
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
			 throw new HttpOutTDTException("proxy");
			 
			 //Put the URL in quarantaine and poll it from time to time until it comes up again. Then we can add it back to the list.
			 //TODO
		    }
	       }

	  }

	  $mods = InstalledResourceFactory::getAllResourceNames();
	  $modindex = -1;
	  foreach($mods as $mod => $resources){
	       //Now that we have all modules, let's search for their methods
	       $modules[$i] = new stdClass();
	       $modules[$i]->resource = array();
	       foreach($resources as $resource){
		    include_once("modules/$mod/$resource.class.php");
		    if(isset($this->mod) && $mod == $this->mod){
			 $modindex=$i;
		    }
		    $mm = new stdClass();
		    $mm->name = $resource;
		    //$mm->doc = $resource::getDoc();
		    //$mm->requiredparameters = $resource::getRequiredParameters();
		    //$mm->parameters = $method::getParameters();
		    //$mm->format = $method::getAllowedPrintMethods();
		    $modules[$i]->resource[] = $mm;
	       }
	       $modules[$i]->name = $mod;
	       $modules[$i]->url = Config::$HOSTNAME . Config::$SUBDIR;
	       $i++;
	  }
	  $o->module = $modules;
	  //check if our modindex has changed, if not, return everything	  
	  if($modindex == -1){
	       return $o;
	  }
	  //otherwise, we will just return this module
	  return $o->module[$modindex];
     }
     
     public function getAllowedPrintMethods(){
	  return array("json","xml", "jsonp", "php");
     }

     public function getDoc(){
	  return "This is a function which will return all supported modules by this API";
     }
}

?>
