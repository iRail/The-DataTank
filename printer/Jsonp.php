<?php
 
  /**
   * This file contains the Jsonp printer.
   * @package The-Datatank/printer
   * @copyright (C) 2011 by iRail vzw/asbl
   * @license AGPLv3
   * @author Jan Vansteenlandt <jan@iRail.be>
   * @author Pieter Colpaert   <pieter@iRail.be>
   */
include_once("printer/Json.php");

/**
 * This class inherits the Json printer. It just needs the json value and it will add
 * some data to make the json into a jsonp message.
 */
class Jsonp extends Json{

     private $callback;

     public function __construct($rootname,$objectToPrint,$callback = ""){
	  if($callback != ""){
	       $this->callback = $callback;
	  }else{
	       throw new PrinterTDTException("With Jsonp you should add a callback: &callback=yourfunctionname");
	  }
	  parent::__construct($rootname,$objectToPrint);
     }

     public function printHeader(){
	  header("Access-Control-Allow-Origin: *");
	  header("Content-Type: application/json;charset=UTF-8");	  
     }

     public function printBody(){
	  echo $this->callback . '(';
	  parent::printBody();
	  echo ')';
     }
};
?>