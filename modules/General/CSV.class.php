<?php
include_once("modules/AMethod.php");
class CSV extends AMethod{

     private $lang,$system;
     private $file = "modules/General/example.csv";

     public function __construct(){
	  parent::__construct("CSV");
     }

     public static function getParameters(){
	  //Add functions to look through CSV file?
	  return array(
	       );
     }

     public static function getRequiredParameters(){
	  //No required Parameters
	  return array();
     }

     public function setParameter($key,$val){
	  $this->$key = $val;
     }

     public function call(){
	  $b = new Test();
	  $d = array();
	  $row = 0;
	  $cols = array();
	  if (($handle = fopen($this->file, "r")) !== FALSE) {
	       while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
		    if($row == 0){
			 for ($i=0; $i < sizeof($data); $i++) {
			      $cols[] = $data[$i];
			 }
		    }else{
			 $r = new Row();
			 for ($i=0; $i < sizeof($data); $i++){
			      $c = $cols[$i];
			      $r->$c = $data[$i];
			 }
			 $d[] = $r;
		    }
		    $row++;
	       }
	       fclose($handle);
	  }
	  $b->row = $d;
	  return $b;
     }
     
     public function allowedPrintMethods(){
	  return array("Xml","Json");
     }

     public static function getDoc(){
	  return "This will read a CSV file: read the first line for the object specification and make an object structure";
     }
}
class Test{
}

class Row{
     
}



?>