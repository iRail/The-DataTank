<?php
abstract class AMethod{
     abstract public function getParameters();
     abstract public function getDoc();
     abstract public function call();
     abstract public function setParameters($params);
     abstract public function allowedPrintMethods();
}

?>