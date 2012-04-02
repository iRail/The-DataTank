<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of OntologyResourceFactory
 *
 * @author mvdrsand
 */
class OntologyFactory extends AResourceFactory {

    public function createCreator($package, $resource, $parameters, $RESTparameters) {
        include_once("model/resources/create/OntologyCreator.class.php");

        $creator = new OntologyCreator($package, $resource, $RESTparameters);
        foreach($parameters as $key => $value){
            $creator->setParameter($key,$value);
        }
        return $creator;
    }

    public function createDeleter($package, $resource, $RESTparameters) {
        include_once("model/resources/delete/OntologyDeleter.class.php");
        $deleter = new OntologyDeleter($package,$resource, $RESTparameters);
        return $deleter;
    }

    public function createReader($package, $resource, $parameters, $RESTparameters) {
        include_once("model/resources/read/OntologyReader.class.php");
        $reader = new OntologyReader($package, $resource, $RESTparameters);
        $reader->processParameters($parameters);
        return $reader;
    }

    protected function getAllResourceNames() {
        $ontologys = OntologyProcessor::getInstance()->getAllOntologys();
        $resources = array();
        foreach($ontologys as $ontology){
            if(!array_key_exists($ontology["baseURI"],$resources)){
        	    $resources[$ontology["baseURI"]] = array();
            }
            $resources[$ontology["baseURI"]][] = $ontology["modelURI"];
        }
        return $resources;
    }

    public function makeCreateDoc($doc) {

    }

    public function makeDeleteDoc($doc) {

    }

    public function makeDoc($doc) {
        
    }


}

?>
