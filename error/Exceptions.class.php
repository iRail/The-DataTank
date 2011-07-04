<?php
/** Copyright (C) 2011 by iRail vzw/asbl
 *
 * These classes extend the Exception class to make our own well-documented Exception-system
 *
 * Author: Pieter Colpaert <pieter aŧ iRail.be>
 * License: AGPLv3
 */


/**
 * The abstract function of TDT Exception
 */
class AbstractTDTException extends Exception{
     abstract public function getDoc();
     abstract public function getErrorCode();

     // Redefine the exception so message isn't optional
     public function __construct($message, $code = 0) {
	  // some code
	  $code = $this->getErrorCode();
	  // make sure everything is assigned properly
	  parent::__construct($message, $code);
     }

     public function customFunction() {
	  echo "A custom function for this type of exception\n";
     }
}

?>