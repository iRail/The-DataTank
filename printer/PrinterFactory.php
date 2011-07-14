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
	  if(($format == "Json" || $format == "Jsonp") && isset($_GET["callback"])){
	       $callback = $_GET["callback"];	    
	       $format = "Jsonp";
	       include_once("printer/$format.php");
	       return new $format($rootname,$objectToPrint,$callback);
	  }
	  include_once("printer/$format.php");
	  return new $format($rootname, $objectToPrint);
     }
}
?>