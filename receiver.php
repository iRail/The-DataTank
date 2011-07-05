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

/*
STEP2
Check if the method exists in some module and fill in the required parameters;
 */
$result;
if(isset($_GET["module"])){
     $module = $_GET["module"];     

     if(isset($_GET["method"])){
	  $method = $_GET["method"];
	  
	  if(file_exists("modules/$module/$method.class.php")){
	       //get the new method
	       include_once("modules/$module/$method.class.php");	       
	       $method = new $method();	       

	       //get all parameters for the method, check and get them from $_GET - array
	       //if a required parameter is not found an exception is thrown.
	       $array = $method->getParameters();	       
	       $parameters;
	       
	       foreach($array as $key){
		    //if a certain parameter is not found, throw exception
		    if(!isset($_GET[$key])){
			 throw new Exception("[ERROR]Key ". $key . " has not been specified.");
		    }
		    $parameters[$key] = $_GET[$key];
	       }
	       //give the necessary parameters to the method
	       $method->setParameters($parameters);

	       //execute the method
	       $result = $method->call();
	       
	  }else{
	       throw new Exception("[ERROR]No such module and or method.",500);
	  }
     }
}else{
     throw new Exception("[ERROR]No such module and or method.",500);
}

/*
STEP 3
Print the result in the preferenced format, or default format
 */


$printer = PrinterFactory::getPrinter($format,$result);

if($printer == NULL){
     throw new Exception("[ERROR]No printer could be made.");
}

$printer->printAll();
?>