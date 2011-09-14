<?php

/**
 * This file contains the RDF/N3 formatter.
 * @package The-Datatank/formatters
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Miel Vander Sande
 */
class Rdf_N3 extends AFormatter {

    public function __construct($rootname, $objectToPrint) {
        parent::__construct($rootname, $objectToPrint);
    }

    protected function printBody() {
        
    }

    protected function printHeader() {
        
    }

    public function printAll() {
        $model = $this->objectToPrint;

        // Import Package Syntax
	include_once(RDFAPI_INCLUDE_DIR.PACKAGE_SYNTAX_N3);
        
        $ser = new N3Serializer();
        
        //important to use $model->getModel() since a serializer works on undelying Model class, not ResModel
        $rdf = & $ser->serialize($model->getModel());
        
        echo $rdf;
    }

}

?>
