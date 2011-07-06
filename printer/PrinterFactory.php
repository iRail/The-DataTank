<?php

include_once("printer/printers/Printer.php");

class PrinterFactory{
    public static function getPrinter($format, $rootname,$objectToPrint){	
	    if(isset($_GET["callback"]) && $format=="json"){
	        $format = "jsonp";
        }
    return new Printer($rootname, $objectToPrint, $format);
    }
}
?>
