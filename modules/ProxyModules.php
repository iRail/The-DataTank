<?php

/**
 * This should become a file which autodetects all modules on other servers.
 * The Federated aspect should care about:
 *    * Interchangability of documentation and stats //done
 *    * Errorhandling through proxy //done
 *    * Extra error if server unavailable and deletion when needed //TODO
 *
 * @package The-Datatank/modules
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert   <pieter@iRail.be>
 * @author Jan Vansteenlandt <jan@iRail.be>
 */

/**
 * This class autodetects all modules on other DataTanks. It should take care of:
 *    * Interchangability of documentation and stats 
 *    * Errorhandling through proxy 
 *    * Extra error if server unavailable and deletion when needed 
 */
class ProxyModules{
     public static $modules = array(
	  "GF" =>  "http://jan.irail.be/GentseFeesten/" // this is a testing datatank
	  );

     /**
      * This function gets all the remote modules stored in this class.
      * @return Array with the modulename mapped on the fully qualified URL to that module.
      */
     public static function getAll(){
	  return self::$modules;
     }
     

     //TODO If this function times out then the module is not reachable, adjust this in the db
     /**
      *  This function makes a proxycall to a remote method and returns the object returned by that remote call.
      */
     public static function call($module, $method, array $args){
	  $modules = self::getAll();

	  //form url with arguments
	  $argstr = "";
	  foreach($args as $key => $val){
	       $argstr .= $key . "=" . $val . "&";
	  }
	  $url = $modules[$module] . $method . "/?" . $argstr;

	  /*
	   * We're going to make two different calls: 1) check if the format is ok
	   *                                          2) call the method
	   * We check the format because we need to do this separatly either way because 
	   * we're passing format=php in the call so that our object can be serialized.
	   * So in order to have a performant way of knowing wether or not a call contains
	   * a valid format, we're calling the documentation of the method and look 
	   * if it's one of the allowed formats.
	   */

	  /*
	   * get all allowed formats of the method
	   */
	  $boom = explode("/",$modules[$module]);
	  $formaturl = "http://".$boom[2]."/TDTInfo/Modules/?format=php&mod=".$boom[3];
	  $formatsobj = unserialize(TDT::HttpRequest($formaturl)->data);
	  
	  // if the format is not allowed throw an exception (functions as proxy error handler). If format is not set, then format will be the standard format and thus no error needs to be thrown 
	  $formatallowed = false;
	  foreach($formatsobj["method"][0]->format as $format){
	       if(strtolower($args["format"]) == strtolower($format)){
		    $formatallowed = true;
		    break;
	       }
	  }

	  if(isset($args["format"]) && !$formatallowed){
	       throw new FormatNotAllowedTDTException("module: ".$module ." method: ".$boom[3],$formatsobj["method"][0]->format);
	  }
	  // if a remote error occurs (error on the remote methodcall) handle it right here
	  // and throw it to an upper layer
	  $request = TDT::HttpRequest($url."format=php");
	  
	  if(isset($request->error)){
	       throw new RemoteServerTDTException($request->data);
	  }
	  
	  $unser_object = unserialize($request->data);
	  //take the first key and return it: otherwise we have the timestamp and version of the other API
	  $keys = array_keys($unser_object);
	  $key = $keys[0];
	  //var_dump($unser_object[$keys[0]]);
	  $o = new stdClass();
	  $o -> $key = $unser_object[$key];
	  return $o;
     }
  }

?>
