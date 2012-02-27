<?php
/**
 * The Kml-formatter is a formatter which will search for location objects throughout the documenttree and return a file with placemarks 
 *
 * @package The-Datatank/formatters
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@iRail.be>
 * @author Pieter Colpaert   <pieter@iRail.be>
 */

/**
 * This class inherits from the abstract Formatter. It will return our resultobject into a kml
 * datastructure.
 */
class KmlFormatter extends AFormatter{

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
          echo "<Document>";

	  $this->printPlacemarks($this->objectToPrint);
          echo "</Document>";

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

     private function xmlgetelement($value){
         $result = "<![CDATA[";
         if(is_object($value)){
             $array = get_object_vars($value);
             foreach($array as $key => $val){
                 if(is_numeric($key)){
                     $key = "int_" . $key;
                 }
                 $result .= "<" . $key . ">" . $val . "</" . $key . ">";
             }
         }else if(is_array($value)){
             foreach($value as $key => $val){
                 if(is_numeric($key)){
                     $key = "int_" . $key;
                 }
                 $result .= "<" . $key . ">" . $val . "</" . $key . ">";
             }
         }else{
             $result .= $value;
         }
         $result .= "]]>";
         return $result;
     }
     

     private function printArray(&$val){
	  foreach($val as $key => &$value){
               $lang = "";
               $lat = "";
               if(is_array($value) && isset($value["lat"]) && isset($value["long"])){
                   $long = $value["long"];
                   $lat = $value["lat"];
                   unset($value["lat"]);
                   unset($value["long"]);
                   $name = $this->xmlgetelement($value); 
               }elseif(is_array($value) && isset($value["Lat"]) && isset($value["Long"])){
                   $long = $value["Long"];
                   $lat = $value["Lat"];
                   unset($value["Lat"]);
                   unset($value["Long"]);
                   $name = $this->xmlgetelement($value); 
               }elseif(is_array($value) && isset($value["longitude"]) && isset($value["latitude"])){
                   
                   $long = $value["longitude"];
                   $lat = $value["latitude"];
                   unset($value["latitude"]);
                   unset($value["longitude"]);
                   $name = $this->xmlgetelement($value); 
               }elseif(is_array($value) && isset($value["Longitude"]) && isset($value["Latitude"])){
                   
                   $long = $value["Longitude"];
                   $lat = $value["Latitude"];
                   unset($value["Latitude"]);
                   unset($value["Longitude"]);
                   $name = $this->xmlgetelement($value); 
               }elseif(is_object($value) && isset($value->Lat) && isset($value->Long)){
                   $long = $value->Long;
                   $lat = $value->Lat;
                   unset($value->Lat);
                   unset($value->Long);
                   $name = $this->xmlgetelement($value); 
               }elseif(is_object($value) && isset($value->lat) && isset($value->long)){
                   $long = $value->long;
                   $lat = $value->lat;
                   unset($value->lat);
                   unset($value->long);
                   $name = $this->xmlgetelement($value); 
               }elseif(is_object($value) && isset($value->Longitude) && isset($value->Latitude)){
                   $long = $value->Longitude;
                   $lat = $value->Latitude;
                   unset($value->Latitude);
                   unset($value->Longitude);
                   $name = $this->xmlgetelement($value); 
               }elseif(is_object($value) && isset($value->longitude) && isset($value->latitude)){
                   $long = $value->longitude;
                   $lat = $value->latitude;
                   unset($value->latitude);
                   unset($value->longitude);
                   $name = $this->xmlgetelement($value); 
               }elseif(is_array($value)){
		    $this->printArray($value);
	       }elseif($value instanceof Location){
                   $this->printPlacemark($value);
	       }//do nothing when key value pair

               if($lat != "" && $long != ""){
                   echo "<Placemark><name>$key</name><Description>".$name."</Description>";
                   echo "<Point><coordinates>".$long.",".$lat."</coordinates></Point></Placemark>";
               }
	  }
     }
     

    public static function getDocumentation(){
        return "Will try to find locations in the entire object and print them as KML points";
    }     
};
?>