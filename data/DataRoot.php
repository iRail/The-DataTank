<?php
/* Copyright (C) 2011 by iRail vzw/asbl */
  /**
   * This is the root of every document. It will specify a version and timestamp. 
   *
   * @package data
   */
class DataRoot{

     private $printer;
     private $rootname;

     public $version;
     public $timestamp;
     /**
      * constructor of this class
      *
      * @param double $version the version of the API
      */
     function __construct($rootname, $version, $error = "") {
	  $this->version = $version;
	  $this->timestamp = date("U");
	  $this->rootname = $rootname;
     }

     public function getRootname(){
	  return $this->rootname;
     }
}

?>
