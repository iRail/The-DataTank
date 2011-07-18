<?php
  /* Copyright (C) 2011 by iRail vzw/asbl
   * Author: Jan Vansteenlandt <jan aŧ iRail.be>
   * Prints the kml style output
   */
include_once("printer/Printer.php");
class Kml extends Printer{

     private $preamble = "";
     

     public function __construct($rootname,$objectToPrint){
	  parent::__construct($rootname,$objectToPrint);
     }

      public function printHeader(){
	  header("Access-Control-Allow-Origin: *");
	  header("Content-Type: text/xml; charset=UTF-8");
     }

     public function printBody(){
	  /*
	   * Print the heading of the kml file then use the recursive functions for the
	   * extended xml which will contain our non kml data. We put a constraint to objects
	   * that are to be written into kml. The object itself( not it's datamembers ) should implement
	   * the Locatable interface. The rest of it's datamembers will then be written in the extended
	   * data part.
	   */
	  

 /*
	   <kml xmlns="http://www.opengis.net/kml/2.2">
	   <Placemark>
	   <name>CampsiteData</name>
	   <!-- Imported schema requires use of namespace prefix -->
	   <ExtendedData xmlns:camp="http://campsites.com">
	   <camp:number>14</camp:number>
	   <camp:parkingSpaces>2</camp:parkingSpaces>
	   <camp:tentSites>4</camp:tentSites>
	   </ExtendedData>
	   <Point>
	   <coordinates>-114.041,53.7199</coordinates>
	   </Point>
	   </Placemark>	   
	   </kml>
	   */
	  echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>";
	  echo "<kml xmlns=\"http://www.opengis.net/kml/2.2\"><Placemark><name>".
	       $this->objectToPrint->getName()."</name>";
	  $this->printExtendedData();
	  echo "<Point><coordinates>".$this->objectToPrint->getLat().
	       ",".$this->objectToPrint->getLong()."</coordinates></Point></Placemark></kml>";
     }

     private function printExtendedData(){
	  /*
	   * Let's see if our object has other properties then just the locatable 
	   * datamembers, if so print them in an extended data xml piece
	   */
	  $hash = get_object_vars($this->objectToPrint);
	  /*
	   * TODO check if get_object_vars also returns private datamembers
	   * php examples shown that get_object_vars return private datamembers but 
	   * without the value. Could be that this is not the case in php 5.3
	   * TODO add global var that contains namespace that needs to be put in front
	   * of every tag in the extendedata sections. (i.e. objectdata:Name ....)
	   */

	  if(is_null($hash)){
	       echo "<ExtendedData xmlns:objectdata=\"http://thedatatank.com/".$_GET["module"].
		    "\">";
	       $this->preamble = "objectdata";
	       foreach($hash as $key => $value){
		    if(is_object($value)){
			 $this->printObject($key,$value);
		    }elseif(is_array($value)){
			 $this->printArray($key,$value);
		    }else{
			 $val = htmlspecialchars($value);
			 echo "<".$this->preamble.":".$key.">".$val."</".$this->preamble.":".$key.">";
		    }
	       }
	       echo "</ExtendedData>";
	  }
     }

     private function printObject($name,$object){
	  echo "<".$this->preamble.":".$name.">";
	  //If this is not an object, it must have been an empty result
	  //thus, we'll be returning an empty tag
	  if(is_object($object)){
	       $hash = get_object_vars($object);
	       foreach($hash as $key => $value){
		    if(is_object($value)){
			 $this->printObject($key,$value);
		    }elseif(is_array($value)){
			 $this->printArray($key,$value);
		    }else{
			 $val = htmlspecialchars($value);
			 echo "<".$this->preamble.":".$key.">". $val ."</".$this->preamble.":".$key.">";
		    }
	       }
	  }
	  // *****  Workaround for array id's:
	  // if an array element has been passed then the name contains id=...
	  // so we need to find the first part of the tag which only contains the name
	  // i.e. $name =>  animal id='0', an explode(" ",$name)[0] should do the trick!
	  $boom = explode(" ",$name);
	  echo "</".$preamble.":".$boom[0].">";
     }

     private function printArray($name,$array){
	  $index = 0;
	  foreach($array as $key => $value){
	       if(is_object($value)){
		    $nametag = $name. " id=\"".$index."\"";
		    $this->printObject($nametag,$value);
	       }else{// no array in arrays are allowed!!
		    if(is_array($value)){
			 throw new InternalPrinterTDTException("Array in an array is trouble with XML-output. Don't do it!");
		    }
		    $value = htmlspecialchars($value);
		     if($this->isHash($array)){
			 echo "<".$preamble.":".$name. " id=\"". $index . "\"><key>".$key."</key><value>".$value."</value></".$this->preamble.":".$name.">";
		     }else{
			  echo "<".$preamble.":".$name. " id=\"". $index . "\">".$value."</".$this->preamble.":".$name.">";
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