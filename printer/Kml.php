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

     public function printBody(){
	  /*
	   * print the KML header first
	   */
	  echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>";
	  echo "<kml xmlns=\"http://www.opengis.net/kml/2.2\">";
	  /*
	   * Second step is to check every locatable object and print it
	   */
	  $this->printPlacemarks($this->objectToPrint);

	  echo "</kml>";
     }
     

     /**
      * The first parameter is the name of an object. The second is an !object!
      */
     private function printPlacemarks($val){
	  $hash = get_object_vars($val);
	  $this->printArray($hash);
     }

     private function printPlacemark($value){
	  echo "<Placemark><name>".$value->getName()."</name>";
	  echo "<Point><coordinates>".$value->getLat().",".$value->getLong()."</coordinates></Point></Placemark>";	  
     }
     

     private function printArray($val){
	  foreach($val as $key =>$value){
	       if($value instanceof Location){
		    $this->printPlacemark($value);
	       }elseif(is_object($value)){
		    $this->printPlacemarks($value);
	       }elseif(is_array($value)){
		    $this->printArray($value);
	       }//do nothing when key value pair
	  }
     }
     
     
};
?>