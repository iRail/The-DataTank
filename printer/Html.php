<?php
/**
 * The Html printer prints everything for development purpose
 *
 * @package The-Datatank/printer
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@iRail.be>
 * @author Pieter Colpaert   <pieter@iRail.be>
 */
include_once("printer/Printer.php");

/**
 * This class inherits from the abstract Printer. It will generate a html-page with a print_r
 */
class Html extends Printer{

     public function __construct($rootname,$objectToPrint){
	  parent::__construct($rootname,$objectToPrint);
     }

      public function printHeader(){
	  header("Access-Control-Allow-Origin: *");
	  header("Content-Type: text/html; charset=UTF-8");
     }

     public function printBody(){
	  include("templates/TheDataTank/header.php");
	  echo "<pre>";
	  print_r($this->objectToPrint);
	  echo "</pre>";
	  include("templates/TheDataTank/footer.php");
     }     
     
};
?>