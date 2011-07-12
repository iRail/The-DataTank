<?php
  /* Copyright (C) 2011 by iRail vzw/asbl */
  /**
   * Author: Jan Vansteenlandt <jan aŧ iRail.be>
   * Prints the Xml style output
   *
   *
   * @package output
   */
include_once("printer/Printer.php");
class Xml extends Printer{
     //private $rootname;
     //make a stack of array information, always work on the last one
     //for nested array support
     private $stack = array();
     private $arrayindices = array();
     private $currentarrayindex = -1;

     public function __construct($rootname,$objectToPrint,$format){
	  parent::__construct($rootname,$objectToPrint,$format);
     }

     public function printHeader(){
	  header("Access-Control-Allow-Origin: *");
	  header("Content-Type: text/xml;charset=UTF-8");
     }

     public function printBody(){
	  $this->printObject(get_class($this->objectToPrint),$this->objectToPrint);
     }

     private function printObject($name,$object){
	  echo "<".$name.">";
	  $hash = get_object_vars($object);
	  foreach($hash as $key => $value){
	       if(is_object($value)){
		    $this->printObject($key,$value);
	       }elseif(is_array($value)){
		    $this->printArray($key,$value);
	       }else{
		    echo "<".$key.">".$value."</".$key.">";
	       }
	  }
	  // if an array element has been passed then the name contains id=...
	  // so we need to find the first part of the tag which only contains the name
	  // i.e. $name =>  animal id='0', an explode(" ",$name)[0] should do the trick!
	  $boom = explode(" ",$name);
	  echo "</".$boom[0].">";
     }

     private function printArray($name,$array){
	  $index = 0;
	  foreach($array as $key => $value){
	       if(is_object($value)){
		    $nametag = $name. " id=\"".$index."\"";
		    $this->printObject($nametag,$value);
	       }else{// no array in arrays are allowed!!
		     if($this->isHash($array)){
			 echo "<".$name. " id=\"". $index . "\"><key>".$key."</key><value>".$value."</value></".$name.">";
		     }else{
			  echo "<".$name. " id=\"". $index . "\">".$value."</".$name.">";
		     }
	       }  
	       $index++;
	  }
     }

     // check if we have an hash or a normal 'numberice array ( php doesn't know the difference btw, it just doesn't care. )
     private function isHash($arr){
	  return array_keys($arr) !== range(0, count($arr) - 1);
     }

};
?>