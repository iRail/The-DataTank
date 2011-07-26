<?php

 /**
   * This file contains the PrinterFactory.
   * @package The-Datatank/printer
   * @copyright (C) 2011 by iRail vzw/asbl
   * @license AGPLv3
   * @author Jan Vansteenlandt <jan@iRail.be>
   * @author Pieter Colpaert   <pieter@iRail.be>
   */

include_once("printer/Printer.php");

/**
 * This class will provide the correct printers (Xml,Kml,php,...)
 */
class PrinterFactory{

     /**
      * This function will return a printer instance of a certain type.
      * @param string $rootname This is needed for some printers.
      * @param string $format   This string will be used to classload the correct printer.
      * @param Mixed  $objectToPrinter This is the object that will be printed.
      * @return Correct printer according to the $format parameter.
      */
     public static function getPrinter($rootname, $format,$objectToPrint){
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