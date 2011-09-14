<?php

/**
 * This class gives the current package to RDF mapping file. This output is for testing purposes only.
 *
 * @package The-Datatank/packages/TDTInfo
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Miel Vander Sande
 */
class Mapping extends AResource {

    // must be set! Contains the value of the module that needs to be analysed.
    private $package;
    // if set only look at certain data from a certain method within the given module.
    private $resource;
    private $mapping;

    public static function getParameters() {
        return array("package" => "Name of a package that needs to be analysed, must be set !",
            "resource" => "Name of a resource within the given package, is not required.",
        );
    }

    public static function getRequiredParameters() {
        return array("package");
    }

    public function call() {
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

    public function processParameters() {
        parent::processParameters();
    }

    public static function getAllowedPrintMethods() {
        return array("html", "rdf_xml", "rdf_ntriple", "rdf_n3", "rdf_json");
    }

    private function getData() {
        $rdfmapper = new RDFMapper($this->package);
        //Build a mapping file for package
        $this->mapping = $rdfmapper->suggestMapping($this->package);
    }

    public static function getDoc() {
        return "Lists a RDF Mapping";
    }

}

?>
