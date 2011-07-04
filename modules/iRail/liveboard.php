<?php
include_once("../AMethod.php");
class Liveboard extends AMethod{

     private $parameters = new array();     

     public function __construct(){
	  echo "constructing Liveboard";
	  $parameters[] = "stationId";
     }

     public function getParameters(){
	  echo "Getting the required parameters.";
	  return $parameters;
     }
}

?>