<?php

/**
 * The Html formatter formats everything for development purpose
 *
 * @package The-Datatank/formatters
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@iRail.be>
 * @author Pieter Colpaert   <pieter@iRail.be>
 * @author Miel Vander Sande 
 */
include_once("formatters/AFormatter.class.php");

/**
 * This class inherits from the abstract Formatter. It will generate a html-page with a print_r
 */
class HtmlFormatter extends AFormatter {

    public function __construct($rootname, $objectToPrint) {
        parent::__construct($rootname, $objectToPrint);
    }

    public function printHeader() {
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: text/html; charset=UTF-8");
    }

    public function printBody() {
        //When the objectToPrint has a property Ontology, it is an RDF Model
        //In this case, use the nice HTML formatting function
        foreach ($this->objectToPrint as $class => $prop){
            if (is_a($prop,"MemModel")){
                $this->objectToPrint = $prop;
                break;
            }
        }
        
        if (is_a($this->objectToPrint,"MemModel")) {
            echo $this->objectToPrint->writeAsHTMLTable();
        } else {
            echo "<pre>";
            print_r($this->objectToPrint);
            echo "</pre>";
        }
    }
    
    public function getDocumentation(){
        return "The Html formatter is a formatter for developing purpose. It prints everything in the internal object.";
    }
    
}

;
?>
