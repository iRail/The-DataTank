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
        //When the objectToPrint is a Model, it is the mapping file amd ready for serialisation.
        //Else it's retrieved data of which we need to build an onthology
        if (!is_a($model, 'Model'))
              $model = RDFOutput::buildRdfOutput($model); 

        // Import Package Syntax
	include_once(RDFAPI_INCLUDE_DIR.PACKAGE_SYNTAX_N3);
        
        $ser = new N3Serializer();
        
        //Serializer only works on MemModel class, so we need to retrieve the underlying MemModel
        if (is_a($model, 'ResModel'))
            $model = $model->getModel();
        if (is_a($model, 'DbModel'))
            $model = $model->getMemModel();
        
        $rdf = & $ser->serialize($model);
        
        echo $rdf;
    }

}

?>
