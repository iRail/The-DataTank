<?php

class PrinterFactory{
     public static function getPrinter($format, $callback){	
	  if(isset($callback) && $format=="Json"){
	       $format = "Jsonp";
	  }
	  if(!file_exists("printers/$format.php")){
	       $format="Xml";
	  }
	  include_once("printers/$format.php");
          //echo "In Pinterfactory format gotten is: ".$format;
	  $printer = new $format(NULL);
	  //$printer->printError($e->getCode(),$e->getMessage());
	  //var_dump($printer);
	  return $printer;
     }
  }
?>