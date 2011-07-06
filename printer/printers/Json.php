<?php
  /* Copyright (C) 2011 by iRail vzw/asbl */
  /**
   * Prints the Json style output
   *
   *
   * @package output
   */
include_once("Printer.php");
class Json extends Printer{
     //private $rootname;
     //make a stack of array information, always work on the last one
     //for nested array support
     private $stack = array();
     private $arrayindices = array();
     private $currentarrayindex = -1;

     public function __construct($rootname,$objectToPrint){
	  $this->rootname = $rootname;
	  $this->objectToPrint = $objectToPrint;
     }
     

     function printHeader(){
	  header("Access-Control-Allow-Origin: *");
	  header("Content-Type: application/json;charset=UTF-8");
     }

     protected function printBody() {
       $hash = get_object_vars($this->objectToPrint);
       $hash['version'] = $this-version;
       $hash['timestamp'] = 0;
       echo json_encode($hash);
     }
     // TODO remove all the abstract stuff, too complext, does nothing but
     // abstracts simplicity

     function printError($ec, $msg){}

     function startRootElement($timestamp){}

     function startArray($name,$number, $root = false){}

     function nextArrayElement(){}

     function startObject($name, $object){}

     function startKeyVal($key,$val){}

     function endArray($name, $root = false){}
     
     function endObject($name){}
     
     function endElement($name){}

     function endRootElement($name){}
};

?>
