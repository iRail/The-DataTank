<?php

include_once("printer/Printer.php");

class PrinterFactory{
    public static function getPrinter($rootname, $format, $rootname,$objectToPrint){
        $callback = null
	    if(isset($_GET["callback"]) && $format=="json"){
            $format = "jsonp";
            $callback = $_GET["callback"];
        }
        
        return new Printer($rootname, $objectToPrint, $format, $callback);
    }
}
?>
