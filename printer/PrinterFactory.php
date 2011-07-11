
<?php
  /* Copyright (C) 2011 by iRail vzw/asbl
   *
   * Author: Jan Vansteenlandt <jan aŧ iRail.be>
   * License: AGPLv3
   *
   * returns the right printer for the right format
   */

include_once("printer/Printer.php");

class PrinterFactory{
     public static function getPrinter($rootname, $format, $rootname,$objectToPrint){
	  $callback = null;
	  if(isset($_GET["callback"]) && $format == "json"){
	       $format = "jsonp";
	       $callback = $_GET["callback"];
	  }

	  return new Printer($rootname, $objectToPrint, $format, $callback);
     }
}
?>