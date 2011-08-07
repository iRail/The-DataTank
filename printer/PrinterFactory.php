<?php
/**
 * This file contains the PrinterFactory. It is a singleton which creates an object of the right formatprinter
 * @package The-Datatank/printer
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@iRail.be>
 * @author Pieter Colpaert   <pieter@iRail.be>
 */

include_once("printer/Printer.php");

/**
 * This class will provide the correct printers (Xml,Kml,php,...)
 */
class PrinterFactory{

    private $format;

    private static $printerfactory;

    /**
     * The constructor will get the right format and will decide which printer should be used to print the object.
     */
    private function __construct(){
	//let's define the format of the output:
	// * First we check the headers for a content-type.
	// * If not given, we'll check the format GET parameter
	// * If not given, standard output is xml
	$headerlines = getallheaders();
	if(isset($headerlines["Content-type"])) {
	    if(preg_match('/.*\/(.*?);.*?/', $headerlines["Content-type"], $matches)) {
		$match = $matches[1];
		//See php doc for this [0] contains the full match, 1 contains the first group
		$this->format = ucfirst(strtolower($match));
	    }
	} elseif(isset($_GET['format'])) {
	    $this->format = ucfirst(strtolower($_GET['format']));
	} else {
	    //default value; if format GET param has not been set, nor 
	    $this->format = 'Xml';
	}
    }
    
    public static function getInstance(){
	if(!isset(self::$printerfactory)){
	    self::$printerfactory = new PrinterFactory();
	}
	return self::$printerfactory;
    }
    

    public function getFormat(){
	return $this->format;
    }

    /**
     * This function will return a printer instance of a certain type.
     * @param string $rootname This is needed for some printers.
     * @param string $format   This string will be used to classload the correct printer.
     * @param Mixed  $objectToPrinter This is the object that will be printed.
     * @return Correct printer according to the $format parameter.
     */
    public function getPrinter($rootname, $objectToPrint){
	$callback = null;
	//this is a fallback for jsonp - if callback is given, just return jsonp anyway
	if(($this->format == "Json" || $this->format == "Jsonp") && isset($_GET["callback"])){
	    $callback = $_GET["callback"];
	    $this->format = "Jsonp";
	    include_once("printer/".$this->format . ".php");
	    return new $format($rootname,$objectToPrint,$callback);
	}
	$format=$this->format;
	include_once("printer/". $this->format . ".php");
	return new $format($rootname, $objectToPrint);
    }
}
?>