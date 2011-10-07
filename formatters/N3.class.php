<?php

/**
 * This file contains the RDF/N3 formatter.
 * @package The-Datatank/formatters
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Miel Vander Sande
 */
class N3 extends AFormatter {

    public function __construct($rootname, $objectToPrint) {
        parent::__construct($rootname, $objectToPrint);
    }

    protected function printBody() {

    }

    protected function printHeader() {

    }

    public function printAll() {
        $model = $this->objectToPrint;
        
        //When the objectToPrint is a MemModel, it is the Ontology and ready for serialisation.
        //Else it's retrieved data of which we need to build an rdf output
        if (!(is_a($model, 'MemModel'))){
            $outputter = new RDFOutput();
            $model = $outputter->buildRdfOutput($model);
        }
            
        // Import Package Syntax
        include_once(RDFAPI_INCLUDE_DIR . PACKAGE_SYNTAX_N3);

        $ser = new N3Serializer();

        $rdf = $ser->serialize($model);

        echo $rdf;
    }

}

?>
