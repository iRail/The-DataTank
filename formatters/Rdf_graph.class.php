<?php
/**
 * This file contains the Graph Image formatter.
 * @package The-Datatank/formatters
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Miel Vander Sande
 */
class Rdf_graph extends AFormatter {
    
    public function __construct($rootname, $objectToPrint) {
        parent::__construct($rootname, $objectToPrint);
    }

    
    protected function printBody() {
        $model = $this->objectToPrint;
        
        //When the objectToPrint is a Model, it is the mapping file amd ready for serialisation.
        //Else it's retrieved data of which we need to build an onthology
        if (!(is_subclass_of($model, 'Model') || is_a($model, 'ResModel')))
            $model = RDFOutput::getInstance()->buildRdfOutput($model);
        
        header('Content-type: image/jpeg');
        $model->visualize('jpeg');
    }

    protected function printHeader() {
        
    }

}

?>
