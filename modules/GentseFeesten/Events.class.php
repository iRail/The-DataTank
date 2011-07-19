<?php

include_once("modules/AMethod.php");
include_once("error/Exceptions.class.php");

class Events extends AMethod{

     private $file = "modules/GentseFeesten/";
     private $day, $hour;
     

     public function __construct(){
	  parent::__construct("Events");
     }

     public static function getRequiredParameters(){
	  return array("day"); //TODO Add your required parameters here
     }

     public static function getParameters(){
	  return array("day"=>"Expects a number [0-10] representing the day of the Gentse Feesten.", "time" => "When time is set, we will return data fur the next hour only");
     }

     public static function getDoc(){
	  return "This method gets all the events for one specific day given with the parameter \"day\".";
	  
     }
     
     public function setParameter($key,$val){
	  if($key == "day"){
	       $this->day = $val;
	  }
     }
     

     public function call(){
	  $b = new stdClass();
          $d = array();
          $row = 0;
	  $this->file.=$this->day.".csv";
          $cols = array("titel","omschrijving","datum","begin","einde","locatie","indoor","plaats","latitude","longitude");
	  
	       if(!file_exists($this->file)){
		    throw new CouldNotGetDataTDTException($this->file);
	       }
	       try{
	       if (($handle = fopen($this->file, "r")) !== FALSE) {
		    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
			 $r = new stdClass();
			 for ($i=0; $i < sizeof($data); $i++){
			      $c = $cols[$i];
			      $r->$c = $data[$i];
			 }
			 $d[] = $r;
			 $row++;
		    }
		    fclose($handle);
	       }else{
		    throw new CouldNotGetDataTDTException($this->file);
	       }
	       
	       $b->event = $d;
	       return $b;
	  }catch(Exception $ex){
	       //file kon nie geopend worden, of er verliep iets fout tijdens het lezen van de file    
	       throw new CouldNotGetDataTDTException($this->file);
	       
	  }
	  //TODO add your businesslogic here, the resulting object will be formatted in an allowed and preferred print method.
     }

     public function allowedPrintMethods(){
	  return array("php","xml","json","jsonp");
     }

     public static function getAllowedPrintMethods(){
	  return array("php","xml","json","jsonp");
     }
     
}
?>