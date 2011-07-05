<?php
abstract class AMethod{
     abstract public function getParameters();
     public static function getDoc(){
	  echo "I'm undocumented :(";
     }
     abstract public function call();
     abstract public function setParameters($params);
     abstract public function allowedPrintMethods();
}

?>