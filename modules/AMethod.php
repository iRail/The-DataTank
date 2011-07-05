<?php
abstract class AMethod{

     public static function getRequiredParameters(){
	  return array();
     }
     public static function getParameters(){
	  return array();
     }
     
     public static function getDoc(){
	  echo "I'm undocumented :(";
     }
     abstract public function call();
     abstract public function setParameters($params);
     abstract public function allowedPrintMethods();
}

?>