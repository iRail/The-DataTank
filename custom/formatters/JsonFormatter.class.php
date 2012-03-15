<?php
/**
 * This file contains the Json printer.
 * @package The-Datatank/formatters
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@iRail.be>
 * @author Pieter Colpaert   <pieter@iRail.be>
 */

/**
 * This class inherits from the abstract Formatter. It will return our resultobject into a
 * json datastructure.
 */
class JsonFormatter extends AFormatter{
     
     public function __construct($rootname,$objectToPrint){
	  parent::__construct($rootname,$objectToPrint);
     }

     public function printHeader(){
	  header("Access-Control-Allow-Origin: *");
	  header("Content-Type: application/json;charset=UTF-8");	  	  
     }

     public function printBody(){
	  if(is_object($this->objectToPrint)){
	       $hash = get_object_vars($this->objectToPrint);
	  }
          
          // some resources are with a capitalcharacter, yet all rootnames are put to lower, so lets check which one
          // we can expect in the hash
          if(!array_key_exists($this->rootname,$hash)){
              $this->rootname = ucfirst($this->rootname);
          }
          

          if(is_object($hash[$this->rootname])){
              echo json_encode($hash[$this->rootname]);
          }else{
              echo json_encode($hash);
          }
	  
     }


     public static function getDocumentation(){
         return "A javascript object notation formatter";
     }
};
?>
