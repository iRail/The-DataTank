<?php

include_once("modules/AMethod.php");

class Events extends AMethod{

     private $file = "modules/GentseFeesten/gentsefeesten.csv";

     public function __construct(){
	  parent::__construct("Events");
     }

     public static function getRequiredParameters(){
	  return array(); //TODO Add your required parameters here
     }

     public static function getParameters(){
	  return array();
	  //TODO Add your all your parameters here with documentation!
	  // i.e. array(param1=>"x-coordinate",param2=>"y-coordinate");
     }

     public static function getDoc(){
	  return "TODO Add your documentation about your module here";
	  
     }
     
     public function setParameter($key,$val){
	  
     }
     

     public function call(){
	  $b = new stdClass();
          $d = array();
          $row = 0;
          $cols = array();
          if (($handle = fopen($this->file, "r")) !== FALSE) {
               while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    if($row == 0){
                         for ($i=0; $i < sizeof($data); $i++) {
                              $cols[] = $data[$i];
                         }
                    }else{
                         $r = new stdClass();
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
          $b->event = $d;
          return $b;
	  return null;
	  //TODO add your businesslogic here, the resulting object will be formatted in an allowed and preferred print method.
     }

     public function allowedPrintMethods(){
	  return array("php","xml","json","jsonp");
	  //TODO add your allowed formats here, i.e. xml,json,kml,...
     }
}
?>