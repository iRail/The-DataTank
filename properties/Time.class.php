<?php
/**
 * Copyright (C) 2011 by iRail vzw/asbl
 * @Author  Jan Vansteenlandt <jan aŧ iRail.be>
 * @Author  Pieter Colpaert <pieter aŧ iRail.be>
 * @License AGPLv3
 *
 * 
 * @Usage $objectmodel->time = new Time(12345678900);
 *
 */

/**
 * Time property 
 */
class Time{

     public $time;

     public function __construct($ut = 0){
	  $this->time = $ut;
     }

     /**
      * TODO: This function sets the time by using an ISO8601 string
      *
      */
     public function setTimeISO8601(string $s){
	  
     }

     public function getUnixTime(){
	  return $this->time;
     }
     
}
?>