<?php
/**
 * This file contains the CSV printer.
 * @package The-Datatank/formatters
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert   <pieter@iRail.be>
 */
include_once("formatters/AFormatter.class.php");

/**
 * This class inherits from the abstract Formatter. It will return our resultobject into a
 * json datastructure.
 */
class CsvFormatter extends AFormatter{
     
     public function __construct($rootname,$objectToPrint){
	  parent::__construct($rootname,$objectToPrint);
     }

     public function printHeader(){
	  header("Access-Control-Allow-Origin: *");
	  header("Content-Type: text/csv;charset=UTF-8");	  	  
     }

     public function printBody(){
         $keys = array_keys(get_object_vars($this->objectToPrint));
         $key = $keys[0];
         $this->objectToPrint = $this->objectToPrint->$key;
         
         if(!is_array($this->objectToPrint)){
             throw new FormatNotAllowedTDTException("You can only request CSV on an array" , array("CSV", "json", "rdf", "xml", "n3","ttl"));
         }
         if(isset($this->objectToPrint[0])){
             //print the header row
             $headerrow = array();
             if(is_object($this->objectToPrint[0])){
                 $headerrow = array_keys(get_object_vars($this->objectToPrint[0]));
             }else{
                 $headerrow = array_keys($this->objectToPrint[0]);
             }

             echo implode(";",$headerrow);
             echo "\n";
             
             foreach($this->objectToPrint as $row){
                 if(is_object($row)){
                     $row = get_object_vars($row);
                 }
                 echo implode(";", $row);
                 echo "\n";
             }
         }         
     }


     public function getDocumentation(){
         return "A CSV formatter. Works only on arrays";
     }
};
?>
