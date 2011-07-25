<?php

/**
 * This file contains the abstract Printer.
 * @package The-Datatank/printer
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert   <pieter@iRail.be>
 * @author Jan Vansteenlandt <jan@iRail.be>
 * @author Werner Laurensse
 */

/**
 * This class is an abstract printer class. It will take an object and prints it to a certain format.
 * This format and the logic to print it in that format will be implemented in a class that inherits from this class.
 */
abstract class Printer {
     protected $rootname;
     protected $objectToPrint;
     protected $format;
     // version of The DataTank API
     protected $version = "1.0";

     /**
      * Constructor.
      * @param string $rootname Name of the rootobject, if used in the print format (i.e. xml)
      * @param Mixed  $objectToPrint Object that needs printing.
      */
     public function __construct($rootname, $objectToPrint) {
	  $this->rootname = $rootname;
	  $this->objectToPrint = $objectToPrint;
     }
     
     /**
      * This function prints the object. uses {@link printHeader()} and {@link printBody()}. 
      */
     public function printAll() {
	  $this->printHeader();
	  $this->printBody();
     }

     /**
      * This function will set the header type of the responsemessage towards the call of the user.
      */
     abstract protected function printHeader();

     /**
      * This function will print the body of the responsemessage.
      */
     abstract protected function printBody();
}
?>