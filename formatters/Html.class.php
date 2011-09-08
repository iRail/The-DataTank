<?php
/**
 * The Html formatter formats everything for development purpose
 *
 * @package The-Datatank/formatters
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@iRail.be>
 * @author Pieter Colpaert   <pieter@iRail.be>
 */
include_once("formatters/AFormatter.class.php");

/**
 * This class inherits from the abstract Formatter. It will generate a html-page with a print_r
 */
class Html extends AFormatter{

     public function __construct($rootname,$objectToPrint){
	  parent::__construct($rootname,$objectToPrint);
     }

      public function printHeader(){
	  header("Access-Control-Allow-Origin: *");
	  header("Content-Type: text/html; charset=UTF-8");
     }

     public function printBody(){
	  echo "<pre>";
	  print_r($this->objectToPrint);
	  echo "</pre>";
     }     
     
};
?>
