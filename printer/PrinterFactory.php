<?php

class PrinterFactory{

     public static function getPrinter($format, $rootname,$objectToPrint){	
	  if(isset($_GET["callback"]) && $format=="Json"){
	       $format = "Jsonp";
	  }
	
	  if(!file_exists("printer/printers/$format.php")){
	       $format="Xml";
	  }
	  include_once("printer/printers/$format.php");
	  //format can be called as a class now.
	  $printer = new $format($rootname,$objectToPrint);
	 
	  return $printer;
     }
  }
?>