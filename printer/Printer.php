<?php


/**
  * An abstract class for a printer. It prints an object
  *
  * @package output
 */
include_once("printer/Formatter.php");
class Printer {
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
     
    function printAll() {
        // Header
        header("Access-Control-Allow-Origin: *");
        if ($this->format == "Json" || $this->format == "Jsonp") {
           //header("Content-Type: text/json;charset=UTF-8"); 
        } else if ($this->format == "Xml") {
            header("Content-Type: text/xml");
        } else if ($this->format == "Kml") {
            header("Content-Type: application/vnd.google-earth.kml+xml");
        } else {
            header("Content-Type: text/plain");
        }
        
        echo Formatter::format($this->format, $this->objectToPrint, $this->version);
    }
}
?>
