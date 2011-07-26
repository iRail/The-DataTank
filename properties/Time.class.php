<?php

/**
   * This file contains the Time propertyclass.
   * @package The-Datatank/properties
   * @copyright (C) 2011 by iRail vzw/asbl
   * @license AGPLv3
   * @author Jan Vansteenlandt <jan@iRail.be>
   * @author Pieter Colpaert   <pieter@iRail.be>
   */

/**
 * Time property, based on the Unixtimestamp.
 */
class Time{

     public $time;

     /*
      * Constructor. 
      * @param integer $ut Time in unix format.
      */
     public function __construct($ut = 0){
	  $this->time = $ut;
     }
     
     // TODO: This function sets the time by using an ISO8601 string
     /*
      * This function sets the time by using an ISO8601 string
      */
     public function setTimeISO8601(string $s){
	  
     }

     /**
      * This functions returns the time in a Unix format.
      * @return Time in unix format.
      */
     public function getUnixTime(){
	  return $this->time;
     }
     
}
?>