<?php
include_once("printer/PrinterFactory.php");
include_once("error/Exceptions.class.php");
include_once("requests/RequestLogger.class.php");
include_once('error/ErrorHandler.class.php');
ini_set('error_reporting', E_ALL);
set_error_handler('wrapper_handler', E_ALL);

$methodname;$format;$result;
try{
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
	       $methodname = $method;

	       if(file_exists("modules/$module/$method.class.php")){
		    //get the new method
		    include_once("modules/$module/$method.class.php");	       
		    $method = new $method();

		    // check if the given format is allowed by the method
		    // if not, throw an exception and return the allowed formats
		    // to the user.
		    if(!in_array($format,$method::allowedPrintMethods())){
			 throw new FormatNotAllowedTDTException($format,$method);
		    }
		    
		    //execute the method
		    $result = $method->call();	       
	       }else{
		    throw new MethodOrModuleNotFoundTDTException($module . "/" .$method);
	       }
	  }
     }else{
	  throw new MethodOrModuleNotFoundTDTException("No module");
     }
     /*
       STEP3
       Now print the bloody thing in our prefered format
      */
     $rootname = $methodname;
     $rootname = strtolower($rootname);
     $printer = PrinterFactory::getPrinter($format,$rootname,$result);
     $printer->printAll();
}catch(Exception $e){
     //Oh noes! An error occured! Let's send this to our error handler
    
     ErrorHandler::logException($e);
 }

//** Jan: Don't we need to put this here? - You've put this at the last part of the try{}.

/*
 STEP 4
 We log this request in any case!
*/
//Currently turned off
//RequestLogger::logRequest();


?>