<?php
/**
 * Copyright (C) 2011 by iRail vzw/asbl
 * Author:  Jan Vansteenlandt <jan aŧ iRail.be>
 * Author:  Pieter Colpaert <pieter aŧ iRail.be>
 * License: AGPLv3
 *
 * This interface implements the basic needs for a kml <Placemark>
 * needs -> name, description Point(= made out of latitude and longitude)
 *
 */

class Location{

     public $long, $lat, $name, $description;

     public function __construct($long,$lat,$name, $description){
	  $this->long = $long;
	  $this->lat = $lat;
	  $this->name = $name;
	  $this->description = $description;
     }

     public function getLong(){
	  return $this->long;
     }
     
     public function getLat(){
	  return $this->lat;
     }

     public function getName(){
	  return $this->name;
     }
     
     public function getDescription(){
	  return $this->description;
     }    
}

?>