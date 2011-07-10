<?php
/* Copyright (C) 2011 by iRail vzw/asbl
 *
 * Author: Werner Laurensse
 * Author: Jan Vansteenlandt <jan aŧ iRail.be>
 * Author: Pieter Colpaert <pieter aŧ iRail.be>
 * License: AGPLv3
 *
 * An abstract class for a printer. It prints an object
 */
include_once("printer/Formatter.php");
class Printer {
     protected $rootname;
     protected $objectToPrint;
     protected $format;
     // version of The DataTank API
     protected $version = "1.0";

     public function __construct($rootname, $objectToPrint, $format) {
	  $this->rootname = $rootname;
	  $this->objectToPrint = $objectToPrint;
	  $this->format = $format;
     }
     
     function printAll() {
	  // Header
	  //CORS: Cross Origin Resource Sharing
	  header("Access-Control-Allow-Origin: *");
	  if($this->format == "Json" || $this->format == "Jsonp") {
	       header("Content-Type: application/json;charset=UTF-8"); 
	  } else if ($this->format == "Xml") {
	       header("Content-Type: text/xml;charset=UTF-8");
	  } else if ($this->format == "Kml") {
	       header("Content-Type: application/vnd.google-earth.kml+xml;charset=UTF-8");
	  } else {
	       header("Content-Type: text/plain;charset=UTF-8");
	  }
	  echo Formatter::format($this->rootname, $this->format, $this->objectToPrint, $this->version);
     }
}
?>
