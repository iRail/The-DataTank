<?php
/**
 * The Kml-printer is a printer which will search for location objects throughout the documenttree and return a file with placemarks 
 *
 * @package The-Datatank/printer
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@iRail.be>
 * @author Pieter Colpaert   <pieter@iRail.be>
 */
include_once("printer/Printer.php");

/**
 * This class inherits from the abstract Printer. It will return our resultobject into a kml
 * datastructure.
 */
class Kml extends Printer{

     public function __construct($rootname,$objectToPrint){
	  parent::__construct($rootname,$objectToPrint);
     }

      public function printHeader(){
	  header("Access-Control-Allow-Origin: *");
	  header("Content-Type: text/xml; charset=UTF-8");
     }

     public function printBody(){// TODO modify this class in order to make it work properly again, after the decision to 
// work with another architecture to represent types in return objects.
	  /*
	   * We will print the KML header first
	   */
	  echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>";
	  echo "<kml xmlns=\"http://www.opengis.net/kml/2.2\">";
	  /*
	   * Second step is to check every locatable object and print it
	   */
	  $this->printPlacemarks($this->rootname, $this->objectToPrint);
          //Print the rest of the file
	  echo "</kml>";
     }
     

     /**
      * The first parameter is the name of an object. The second is an !object!
      */
     private function printPlacemarks($key, $val){
	  $hash = get_object_vars($val);
	  foreach($hash as $key => $value){
	       if($value instanceof Location){
		    $this->printPlacemark($value);
	       }elseif(is_object($value)){
		    $this->printPlacemarks($key,$value);
	       }elseif(is_array($value)){
		    $this->printArray($key,$value);
	       }//do nothing when key value pair
	  }
     }

     private function printPlacemark($value){
	  echo "<Placemark><name>".$value->getName()."</name>";
	  echo "<Point><coordinates>".$value->getLat().",".$value->getLong()."</coordinates></Point></Placemark>";	  
     }
     

     private function printArray($key,$val){
	  foreach($val as $key =>$value){
	       if($value instanceof Location){
		    $this->printPlacemark($value);
	       }elseif(is_object($value)){
		    $this->printPlacemarks($key,$value);
	       }elseif(is_array($value)){
		    $this->printArray($key,$value);
	       }//do nothing when key value pair
	  }
     }
     
     
//     private function printExtendedData(){
//	  /*
//	   * Let's see if our object has other properties then just the locatable 
//	   * datamembers, if so print them in an extended data xml piece
//	   */
//	  $hash = get_object_vars($this->objectToPrint);
//	  /*
//	   * TODO check if get_object_vars also returns private datamembers
//	   * php examples shown that get_object_vars return private datamembers but 
//	   * without the value. Could be that this is not the case in php 5.3
//	   * TODO add global var that contains namespace that needs to be put in front
//	   * of every tag in the extendedata sections. (i.e. objectdata:Name ....)
//	   */
//
//	  if(is_null($hash)){
//	       echo "<ExtendedData xmlns:objectdata=\"http://thedatatank.com/".$_GET["module"].
//		    "\">";
//	       $this->preamble = "objectdata";
//	       foreach($hash as $key => $value){
//		    if(is_object($value)){
//			 $this->printObject($key,$value);
//		    }elseif(is_array($value)){
//			 $this->printArray($key,$value);
//		    }else{
//			 $val = htmlspecialchars($value);
//			 echo "<".$this->preamble.":".$key.">".$val."</".$this->preamble.":".$key.">";
//		    }
//	       }
//	       echo "</ExtendedData>";
//	  }
//     }
//
//
//     /**
//      * Is this really duplicated code? Shouldn't we just extend from Xml.class.php?
//      */
//     private function printObject($name,$object){
//	  echo "<".$this->preamble.":".$name.">";
//	  //If this is not an object, it must have been an empty result
//	  //thus, we'll be returning an empty tag
//	  if(is_object($object)){
//	       $hash = get_object_vars($object);
//	       foreach($hash as $key => $value){
//		    if(is_object($value)){
//			 $this->printObject($key,$value);
//		    }elseif(is_array($value)){
//			 $this->printArray($key,$value);
//		    }else{
//			 $val = htmlspecialchars($value);
//			 echo "<".$this->preamble.":".$key.">". $val ."</".$this->preamble.":".$key.">";
//		    }
//	       }
//	  }
//	  // Workaround for array id's:
//	  // if an array element has been passed then the name contains id=...
//	  // so we need to find the first part of the tag which only contains the name
//	  // i.e. $name =>  animal id='0', an explode(" ",$name)[0] should do the trick!
//	  $boom = explode(" ",$name);
//	  echo "</".$preamble.":".$boom[0].">";
//     }
//
//     private function printArray($name,$array){
//	  $index = 0;
//	  foreach($array as $key => $value){
//	       if(is_object($value)){
//		    $nametag = $name. " id=\"".$index."\"";
//		    $this->printObject($nametag,$value);
//	       }else{// no array in arrays are allowed!!
//		    if(is_array($value)){
//			 throw new InternalPrinterTDTException("Array in an array is trouble with XML-output. Don't do it!");
//		    }
//		    $value = htmlspecialchars($value);
//		     if($this->isHash($array)){
//			 echo "<".$preamble.":".$name. " id=\"". $index . "\"><key>".$key."</key><value>".$value."</value></".$this->preamble.":".$name.">";
//		     }else{
//			  echo "<".$preamble.":".$name. " id=\"". $index . "\">".$value."</".$this->preamble.":".$name.">";
//		     }
//	       }  
//	       $index++;
//	  }
//     }
//
//     // check if we have an hash or a normal 'numberice array ( php doesn't know the difference btw, it just doesn't care. )
//     private function isHash($arr){
//	  return array_keys($arr) !== range(0, count($arr) - 1);
//     }*/
};
?>