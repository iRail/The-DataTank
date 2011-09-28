<?php

/**
 * This class gives the current package to RDF mapping file. This output is for testing purposes only.
 *
 * @package The-Datatank/packages/TDTInfo
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Miel Vander Sande
 */
class Mapping extends AReader{

    private $mapping;

    public function __construct($package,$resource){
        parent::__construct($package,$resource);
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
        return $this->mapping;
    }

    public function setParameter($key, $val) {
        if ($key == "package") {
            $this->package = $val;
        } elseif ($key == "resource") {
            $this->resource = $val;
        }
    }

    public static function getAllowedFormatters() {
        return array("html", "rdf_xml", "rdf_ntriple", "rdf_n3", "rdf_json");
    }

    private function getData() {
        $rdfmapper = new RDFMapper();
        //Build a mapping file for package
        $this->mapping = $rdfmapper->getMappingModel($this->package)->getModel()->getMemModel();
    }

    public static function getDoc() {
        return "Lists a RDF Mapping";
    }

}

?>
