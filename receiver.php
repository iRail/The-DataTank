<?php

include_once("printer/PrinterFactory.php");

//error_reporting(-1);
/*
STEP1
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
     throw new Exception("[ERROR]No printer could be made.");
}
//TODO rewrite Xml (extends Printer) -> contains lots of iRail-dependant code
//first we'll focus our ninja skillz on the abstraction of Methods and AMethod in the 
//directory Modules
//$printer->printAll();

/*
STEP2
Check if the method exists in some module and fill in the required parameters;
 */
if(isset($_GET["module"])){
     $module = $_GET["module"];
     echo "module is " . $module . "\n";
     if(isset($_GET["method"])){
	  $method = $_GET["method"];
	  echo "method is " . $method . "\n";
	  if(file_exists("modules/$module/$method.php")){
	       //get a new method
	       $method = new $method(NULL);
	       //get all parameters for the method, check and get them from $_GET - array
	       $array = $method->getParameters();
	       echo "Got all the parameters for the method". "\n.";
	       foreach($array as $key){
		    echo "key : " . $key . "\n";
	       }
	  }else{
	       throw new Exception("[ERROR]No such module and or method.",500);
	  }
     }
}else{
     throw new Exception("[ERROR]No such module and or method.",500);
}
?>