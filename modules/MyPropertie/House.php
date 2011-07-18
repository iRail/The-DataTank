<?php
include_once('types/Locatable.php');

class House implements Locatable{

     public $value;
     public $age;

     public function __construct(){
	  $this->value = '1000USD';
	  $this->age = '19';
     }

     public function getLong(){
	  return 53.067222;
     }
     
     public function getLat(){
	  return 3.350278;
     }

     public function getDescription(){
	  return "House full of joy and happiness....and wild stuff *wink wink*";
     }
     
     public function getName(){
	  return "PB Mansion";
     }
}

?>