<?php
/**This is a stub, don't use it**/
include_once("Printer.php");
class Kml extends Printer{
     private $ATTRIBUTES=array("id", "locationX", "locationY", "standardname", "left","delay", "normal");
     private $rootname;

     public function __construct(){
	  $this->rootname = $rootname;
	  $this->objectToPrint = $objectToPrint;
     }

     function printHeader(){
	  header("Access-Control-Allow-Origin: *");
	  header("Content-Type: application/vnd.google-earth.kml+xml");
	  echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";

     }

     function printError($ec, $msg){
	  $this->printHeader();
	  header("HTTP/1.1 $ec $msg");
	  echo "<error code=\"$ec\">$msg</error>";
     }
     
     function startRootElement($name, $version, $timestamp){
	  $this->rootname = $name;
	  if($name == "stations"){
	       echo "<kml xmlns=\"http://www.opengis.net/kml/2.2\">";
	  }else{
	       $this->printError(400,"KML only works for stations at this moment");
	  }
     }
//make a stack of array information, always work on the last one
//for nested array support
     private $stack = array();
     private $arrayindices = array();
     private $currentarrayindex = -1;
     function startArray($name,$number, $root = false){
     }

     function nextArrayElement(){
	  $this->arrayindices[$this->currentarrayindex]++;
     }

     function startObject($name, $object){
	  if($name == "station"){
	       echo "<Placemark id='". $object->id ."'><name>". $object->name ."</name><Point><coordinates>". $object->locationX .",". $object->locationY."</coordinates></Point></Placemark>";
	  }
     }

     function startKeyVal($key,$val){
     }

     function endElement($name){
     }
     function endArray($name, $root = false){
     }

     function endRootElement($name){
	  echo "</kml>";
     }
};
?>