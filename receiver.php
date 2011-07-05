<?php
ini_set('include_path', '.');

include_once("printer/PrinterFactory.php");
include_once("error/Exceptions.class.php");

ini_set('error_reporting', E_ALL);


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
	  
	       if(file_exists("modules/$module/$method.class.php")){
		    //get the new method
		    include_once("modules/$module/$method.class.php");	       
		    $method = new $method();	       

		    //get all parameters for the method, check and get them from $_GET - array
		    //if a required parameter is not found an exception is thrown.
		    $array = $method->getRequiredParameters();
		    $parameters;
	       
		    foreach($array as $key){
			 //if a certain parameter is not found, throw exception
			 if(!isset($_GET[$key])){
			      throw new ParameterTDTException($key);
			 }
			 $parameters[$key] = $_GET[$key];
		    }
		    //give the necessary parameters to the method
		    $method->setParameters($parameters);

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
  STEP 3
  Print the result in the preferenced format, or default format
*/
     $dummyrootname = "root";
     $printer = PrinterFactory::getPrinter($format,$dummyrootname,$result);
     $printer->printAll();

}catch(Exception $e){
     //Oh noes! An error occured! Let's send this to our error handler
     //TO DO
     echo $e->getMessage();
}


?>