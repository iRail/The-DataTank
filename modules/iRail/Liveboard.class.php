<?php
include_once("modules/AMethod.php");
class Liveboard extends AMethod{

     private $parameters;

     public function __construct(){
	  echo "constructing Liveboard";
	  $this->parameters = "stationId";
     }

     public function getParameters(){
	  echo "Getting the required parameters.";
	  return $this->parameters;
     }
}

?>