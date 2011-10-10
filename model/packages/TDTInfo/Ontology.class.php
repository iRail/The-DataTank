<?php

/**
 * This class gives access to the onthology of resources
 *
 * @package The-Datatank/packages/TDTInfo
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Miel Vander Sande
 */
class Ontology extends AReader{

    private $ontology;

    public function __construct($package, $resource, $RESTparameters) {
        parent::__construct($package, $resource, $RESTparameters);
    }

    public static function getParameters() {
        return array("package" => "Name of a package that needs to be analysed, must be set !",
            "resource" => "Name of a resource within the given package, is not required.",
             
        );
    }

    public static function getRequiredParameters() {
        return array("package");
    }

    public function read() {
        $this->getData();
        return $this->ontology;
    }

    public function setParameter($key, $val) {
        if ($key == "package") {
            $this->package = $val;
        } elseif ($key == "resource") {
            $this->resource = $val;
        } 
    }
    


        public static function getAllowedFormatters() {
        return array();
    }

    private function getData() {
        $filename = "custom/packages/" . $this->package."/".$this->package.".ttl";
        
        if (file_exists($filename)){
            OntologyProcessor::getInstance()->readOntologyFile($this->package, $filename);
        }
        $this->ontology = OntologyProcessor::getInstance()->readOntology($this->package);
    }

    public static function getDoc() {
        return "Lists a package ontology";
    }

}

?>
