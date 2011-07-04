<?php

include_once("printer/PrinterFactory.php");


/*
Get the format and callback keys and values, 
pass them to our PrinterFactory which returns our printer, if possible ofc.
 */
$format = "";
$callback = NULL;

if(isset($_GET["format"])){
     $format = $_GET["format"];
}

if($format == ""){
     $format = "Xml";
}
//make sure the first letter is uppercase and the rest is lowercase
$format = ucfirst(strtolower($format));

if(isset($_GET["callback"])){
     $callback = "";
}

$printer = PrinterFactory::getPrinter($format,$callback);
//$printer->printHeader();
if($printer == NULL){
     //TODO error handling
}
$printer->printAll();


?>