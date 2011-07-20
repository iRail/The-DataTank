<?php
/* Copyright (C) 2011 by iRail vzw/asbl
 *
 * Author: Pieter Colpaert <pieter aŧ iRail.be>
 * License: AGPLv3
 *
 * This should become a file which autodetects all modules on other servers.
 * The Federated aspect should care about:
 *    * Interchangability of documentation and stats //done
 *    * Errorhandling through proxy //in progress
 *    * Extra error if server unavailable and deletion when needed //TODO
 */


/**
 * An array of all known services
 */
class ProxyModules{
     public static $modules = array(
	  //  "StatsJan" => "http://172.22.32.119/TDTInfo/",
	     "GF" =>  "http://jan.irail.be/GentseFeesten/"
	  // "Pieter" => "http://171.22.32.50/"
	  );

     /**
      * Should fetch the active proxy modules out of the database
      */
     public static function getAll(){
	  return self::$modules;
     }
     
     /**
      *  Make a proxycall
      *  If timeout, then we should put the module on inactive in the db
      */
     public static function call($module, $method, array $args){
	  $modules = self::getAll();
	  $argstr = "";
	  foreach($args as $key => $val){
	       $argstr .= $key . "=" . $val . "&";
	  }
	  //$argstr = rtrim("&",$argstr);
	  $url = $modules[$module] . $method . "/?" . $argstr;	  
	  //do call
	  /*
	   * get all the allowed formats of the method
	   */
	  $boom = explode("/",$modules[$module]);
	  $formaturl = "http://".$boom[2]."/TDTInfo/Modules/?format=php&mod=".$boom[3];
	  //var_dump(TDT::HttpRequest($formaturl));
	  $formatsobj = unserialize(TDT::HttpRequest($formaturl)->data);
	  
	  /*
	   * We're going to make two different calls: 1) check if the format is ok
	   *                                          2) call the method
	   * We check the format because we need to do this separatly either way because 
	   * we're passing format=php in the call so that our object can be serialized.
	   * So in order to have a performant way of knowing wether or not a call contains
	   * a valid format, we're calling the documentation of the method and look 
	   * if it's one of the allowed formats.
	   */
	  // if the format is not allowed throw an error (functions as proxy error handler). If format is not set, then format will be the standard format and thus no error needs to be thrown 
	  if(isset($args["format"]) && !in_array($args["format"],$formatsobj["method"][0]->format)){
	       throw new FormatNotAllowedTDTException("module: ".$module ." method: ".$boom[3]
						      ,$formatsobj["method"][0]->format);
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
