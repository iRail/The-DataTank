<?php
  /* Copyright (C) 2011 by iRail vzw/asbl */
  /**
   * Author: Jan Vansteenlandt <jan aŧ iRail.be>
   * Prints the Jsonp style output
   *
   *
   * @package output
   */
include_once("printer/Json.php");
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