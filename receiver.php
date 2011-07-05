<?php
ini_set('include_path', '.');

include_once("printer/PrinterFactory.php");
//Set error_reporting to high. We should not receive any errors here!
ini_set('error_reporting', E_ALL);


/*
STEP1
Get the format and callback keys and values, 
pass them to our PrinterFactory which returns our printer, if possible ofc.
 */

$format = "";

if(isset($_GET["format"])){
     $format = $_GET["format"];
}

if($format == ""){
     $format = "Xml";
}

//make sure the first letter is uppercase and the rest is lowercase
$format = ucfirst(strtolower($format));

$printer = PrinterFactory::getPrinter($format);
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
	  if(file_exists("modules/$module/$method.class.php")){	      
	       //get a new method
	       include_once("modules/$module/$method.class.php");	       
	       $method = new $method();
	       echo "Parameters necessary for the method: ". "\n.";
	       //get all parameters for the method, check and get them from $_GET - array
	       $array = $method->getParameters();	       
	       foreach($array as $key){
		    echo " key: " . $key;
		    $value = $_GET["$key"];
		    echo " value: " . $value;
	       }
	  }else{
	       echo "File doesn't exist.";
	       throw new Exception("[ERROR]No such module and or method.",500);
	  }
     }
}else{
     throw new Exception("[ERROR]No such module and or method.",500);
}
?>