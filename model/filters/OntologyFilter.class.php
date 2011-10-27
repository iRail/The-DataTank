<?php

/**
 * This class represents the RESTful lookup of an Ontology
 *
 * @package The-Datatank/filters
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Miel Vander Sande
 */
class OntologyFilter extends AFilter {

    public function __construct($params) {
        parent::__construct($params);
    }

    public function filter($result) {
        if (OntologyProcessor::getInstance()->isOntology($result)) {
            $path = implode('/', $this->params);
            //$new_result = $result->findWildcarded(str_replace('/', '\/', $path) . '%', null, null);
            
            $new_result = $result->findRegex('/'.str_replace('/', '\/', $path) . '.*/', null, null);
            
            
            if (count($new_result->triples) == 0)
                    throw new RESTTDTException($path.' does not exist.');
            $new_result->setBaseURI($result->getBaseURI());
            
            //add onthology header to the filtered result
            $base_resource = new Resource($new_result->getBaseURI().$path);
            $description = new Literal("Ontology of ".$path." in The DataTank",null,'datatype:STRING');
            $new_result->add(new Statement($base_resource,RDF::TYPE(),  OWL::ONTOLOGY()));
            $new_result->add(new Statement($base_resource,RDFS::COMMENT(),  $description));
            
            return $new_result;
        }
    }

}

?>
