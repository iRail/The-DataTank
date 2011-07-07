<?php

include_once("printer/Printer.php");

class PrinterFactory{
    public static function getPrinter($rootname, $format, $rootname,$objectToPrint){	
	    if(isset($_GET["callback"]) && $format=="json"){
	        $format = "jsonp";
        }
    return new Printer($rootname, $objectToPrint, $format);
    }
}
?>
