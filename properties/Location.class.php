<?php

 /**
   * This file contains the Location propertyclass.
   * @package The-Datatank/properties
   * @copyright (C) 2011 by iRail vzw/asbl
   * @license AGPLv3
   * @author Jan Vansteenlandt <jan@iRail.be>
   * @author Pieter Colpaert   <pieter@iRail.be>
   */

  /**
   * This class contains the basic needs for a kml <Placemark>.
   */
class Location{

     public $long, $lat, $name, $description;

     /**
      * Constructor.
      * @param integer $long Integer representing the longitude.
      * @param integer $lat  Integer representing the latitude.
      * @param string  $name String providing information about the placemark data.
      * @param string  $description String providing a description about the Location.
      */
     public function __construct($long,$lat,$name, $description){
	  $this->long = $long;
	  $this->lat = $lat;
	  $this->name = $name;
	  $this->description = $description;
     }

     /**
      * This function gets the longitude of the Location property.
      * @return Longitude of the Location property.
      */
     public function getLong(){
	  return $this->long;
     }


     /**
      * This function gets the latitude of the Location property.
      * @return Latitude of the Location property.
      */     
     public function getLat(){
	  return $this->lat;
     }

     
     /**
      * This function gets the name of the Location property.
      * @return Name of the Location property.
      */
     public function getName(){
	  return $this->name;
     }
     

     /**
      * This function gets the Description of the Location property.
      * @return Description of the Location property.
      */
     public function getDescription(){
	  return $this->description;
     }    
}

?>