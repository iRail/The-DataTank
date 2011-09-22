<?php

/**
 * This file contains the RDF/JSON formatter.
 * @package The-Datatank/formatters
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Miel Vander Sande
 */
class Rdf_json extends AFormatter {

    public function __construct($rootname, $objectToPrint) {
        parent::__construct($rootname, $objectToPrint);
    }

    protected function printBody() {
        
    }

    protected function printHeader() {
        
    }

    public function printAll() {
        $model = $this->objectToPrint;

        //When the objectToPrint is a MemModel, it is the mapping file and ready for serialisation.
        //Else it's retrieved data of which we need to build an rdf output
        if (!(is_a($model, 'MemModel')))
            $model = RDFOutput::getInstance()->buildRdfOutput($model);

        // Import Package Syntax
        include_once(RDFAPI_INCLUDE_DIR . PACKAGE_SYNTAX_JSON);

        $ser = new JsonSerializer();

        $rdf = $ser->serialize($model);

        echo $rdf;
    }

}

?>
