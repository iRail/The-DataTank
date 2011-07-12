<?php
  /* Copyright (C) 2011 by iRail vzw/asbl
   *
   * Author: Werner Laurensse
   * Author: Jan Vansteenlandt <jan aŧ iRail.be>
   * Author: Pieter Colpaert   <pieter aŧ iRail.be>
   * License: AGPLv3
   *
   * An abstract class for a printer. It prints an object
   */
include_once("printer/Formatter.php");
abstract class Printer {
     protected $rootname;
     protected $objectToPrint;
     protected $format;
     // version of The DataTank API
     protected $version = "1.0";

     public function __construct($rootname, $objectToPrint, $format) {
	  $this->rootname = $rootname;
	  $this->objectToPrint = $objectToPrint;
	  $this->format = $format;
     }
     
     public function printAll() {
	  $this->printHeader();
	  $this->printBody();
     }

     abstract protected function printHeader();
     abstract protected function printBody();
}
?>