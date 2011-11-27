<?php
/**
 * This file contains the FormatterFactory. It is a singleton which creates an object of the right formatprinter
 * @package The-Datatank/formatters
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@iRail.be>
 * @author Pieter Colpaert   <pieter@iRail.be>
 */

include_once("formatters/AFormatter.class.php");

/**
 * This class will provide the correct printers (Xml,Kml,php,...)
 */
class FormatterFactory{

    private $format;

    private static $formatterfactory;

    public static function getInstance($urlformat = ""){
	if(!isset(self::$formatterfactory)){
	    self::$formatterfactory = new FormatterFactory($urlformat);
	}
	return self::$formatterfactory;
    }    


    public function setFormat($urlformat){
        //We define the format like this:
        // * Check if $urlformat has been set
        //   - if not: probably something fishy happened, set format as error for logging purpose
        //   - else if is about: do content negotiation
        //   - else check if format exists 
        //        × throw exception when it doesn't
        //        × if it does, set $this->format with ucfirst

        //first, let's be sure about the case of the format
        $urlformat = ucfirst(strtolower($urlformat));
        
        if($urlformat == ""){
            $this->format = "error";
        }else if(strtolower($urlformat) == "about"){
            include_once("formatters/ContentNegotiator.class.php");
            $cn = ContentNegotiator::getInstance();
            $format = $cn->pop();
            while(!$this->formatExists($format) && $cn->hasNext()){
                $format = $cn->pop();
                if($format == "*"){
                    $format == "Xml";
                }
            }
            if(!$this->formatExists($format)){
                throw new FormatNotFoundTDTException($format); // could not find a suitible format
            }
            $this->format = $format;
            //We've found our format through about, so let's set the header for content-location to the right one
            //to do this we're building our current URL and changing .about in .format
            $format= strtolower($this->format);
            $pageURL = 'http';
            if (isset($_SERVER["HTTPS"])) {$pageURL .= "s";}
            $pageURL .= "://";
            if ($_SERVER["SERVER_PORT"] != "80") {
                $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
            } else {
                $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
            }
            $contentlocation = str_replace(".about", "." . $format, $pageURL);
            header("Content-Location:" . $contentlocation);
        }else if($this->formatExists($urlformat)){
            $this->format = $urlformat;
        }else{
            throw new FormatNotFoundTDTException($urlformat);
        }
        
    }
    

    /**
     * The constructor will get the right format and will decide which printer should be used to print the object.
     */
    private function __construct($urlformat = ""){
        $this->setFormat($urlformat);
    }

    private function formatExists($format){
        return file_exists("formatters/". $format . "Formatter.class.php"); // || file_exists("custom/formatters/". $format . ".class.php"):
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
	    include_once("formatters/".$this->format . "Formatter.class.php");
	    return new $this->format($rootname,$objectToPrint,$callback);
	}
	$format=$this->format."Formatter";
	include_once("formatters/". $this->format . "Formatter.class.php");
	return new $format($rootname, $objectToPrint);
    }


    //todo
    private function getAllFormatters(){
        
    }
    
    
    /**
     * This will fetch all the documentation from the formatters and put it into the documentation visitor //todo
     */
    public function getDocumentation($doc){
        
    }
}
?>
