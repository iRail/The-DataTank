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
        $model->visualize('gif');
    }

    protected function printHeader() {
        header('Content-type: image/gif');
    }

}

?>
