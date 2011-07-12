<?php
  /* Copyright (C) 2011 by iRail vzw/asbl */
  /**
   * Author: Jan Vansteenlandt <jan aŧ iRail.be>
   * Prints the Jsonp style output
   *
   *
   * @package output
   */
include_once("printer/Printer.php");
class Json extends Printer{

     private $callback;

     public function __construct($rootname,$objectToPrint,$format,$callback){
	  parent::__construct($rootname,$objectToPrint,$format);
     }

     public function printHeader(){
	  header("Access-Control-Allow-Origin: *");
	  header("Content-Type: application/json;charset=UTF-8");	  
     }

     public function printBody(){
	  if(is_object($this->objectToPrint)){
	       $hash = get_object_vars($this->objectToPrint);
	       $hash['version'] = $this->version;
	       $hash['timestamp'] = time();
	       
	       echo $callback . '(' . json_encode($hash) . ')';
	  }else{
	       throw new PrinterTDTException("The object given is NULL");
	  }
     }
};
?>