<?php
/* Copyright (C) 2011 by iRail vzw/asbl
 *
 * Author: Pieter Colpaert <pieter aŧ iRail.be>
 * License: AGPLv3
 *
 * This should become a file which autodetects all modules on other servers.
 * The Federated aspect should care about:
 *    * Interchangability of documentation and stats
 *    * Errorhandling through proxy
 *    * Extra error if server unavailable and deletion when needed
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
	  // $argstr = rtrim("&",$argstr);
	  $url = $modules[$module] . $method . "/?" . $argstr;	  
	  //do call
	  $answer = TDT::HttpRequest($url);
	  var_dump($answer);
	  
	  if(isset($answer->error)){
	       throw new Exception($answer->error,$answer->code);
	  }
	  
	  $unser_object = unserialize(TDT::HttpRequest($url . "format=php")->data);
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
