<?php

/**
 * This class OnthologyProcessor maps resources from a package to RDF classes. It handles ontologies and modifications from the user,
 *
 * @package The-Datatank/model/semantics
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Miel Vander Sande
 */
include_once('RDFConstants.php');
include_once('tdtml/TDTML.class.php');

class OntologyProcessor {

    private static $uniqueinstance;

    private function __construct() {
        
    }

    public static function getInstance() {
        if (!isset(self::$uniqueinstance)) {
            self::$uniqueinstance = new OntologyProcessor();
        }
        return self::$uniqueinstance;
    }

    /**
     *
     * This function reads an ontology turtle file for the first time
     *
     */
    public function readOntologyFile($package, $filename) {
        $this->getModel($package)->load($filename, "n3");
    }

    private function setPreferred(& $model) {
        
    }

    public function isOntology($model) {
        $result = $model->findFirstMatchingStatement(null, RDF::TYPE(), OWL::ONTOLOGY());

        if (is_null($result))
            return false;
        else
            return true;
    }

    public function hasOntology($package) {
        $store = RbModelFactory::getRbStore();
        return $store->modelExists($this->getOntologyURI($package));
    }

    //CRUD METHODS for whole Ontology

    public function updateOntology($package) {
        
    }

    public function createOntology($package) {
        $this->getModel($package);
        return true;
    }

    public function readOntology($package) {
        return $this->getModel($package)->getMemModel();
    }

    public function deleteOntology($package) {
        $this->getModel($package)->delete();
    }

    //CRUD METHODS for paths in Ontology

    public function updatePathMap($package, $path, $value) {
        $resource = new Resource($path);
        $mapping = new Resource($value);

        $statement = null;
        if ($this->isPathProperty($path))
            $statement = new Statement($resource, OWL::EQUIVALENT_PROPERTY(), $mapping);
        else
            $statement = new Statement($resource, OWL::EQUIVALENT_CLASS(), $mapping);
        var_dump($package);
        $this->getModel($package)->add($statement);
    }

    public function updatePathPreferredMap($package, $path, $value) {
        $resource = new Resource($path);
        $mapping = new Resource($value);

        $statement = null;
        if ($this->isPathProperty($path))
            $statement = new Statement($resource, TDTML::PREFERRED_PROPERTY(), $mapping);
        else
            $statement = new Statement($resource, TDTML::PREFERRED_CLASS(), $mapping);

        $this->getModel($package)->add($statement);
    }

    public function createPath($package, $path) {
        $resource = new Resource($path);

        $statement = null;
        if ($this->isPathProperty($path))
            $statement = new Statement($resource, RDF::TYPE(), RDF::PROPERTY());
        else
            $statement = new Statement($resource, RDF::TYPE(), OWL::OWL_CLASS());

        $this->getModel($package)->add($statement);
    }

    public function readPath($package, $path) {
        $param = str_replace('/', '\/', $path) . '%';
        return $this->getModel($package)->findWildcarded($param, null, null);
    }

    public function deletePath($package, $path) {
        $temp = $this->readPath($package, $path);
        foreach ($temp->triples as $statement) {
            echo $this->getModel($package)->remove($statement);
        }
    }

    //functions for retrieving mapping
    public function getClassMap($package, $path) {
        $ontology = $this->getModel($package);
        $path = $this->trimPath($path);

        //$namespaces = $ontology->getParsedNamespaces();
        //$statement = $ontology->findFirstMatchingStatement(new Resource($path), OWL::EQUIVALENT_CLASS(), null);
        $statement = new Statement(new Resource("http://www.something/ont#TestClass"), new Resource("http://www.something/ont#TestClass"), new Resource("http://www.something/ont#TestClass"));
        if (!is_null($statement)) {
            $result = new stdClass ();
            $result->class = $statement->getObject();
            //$result->prefix = $namespaces[$statement->getObject()->getNamespace()];
            return $result;
        }

        return false;
    }

    public function getPropertyMap($package, $path) {
        $ontology = $this->getModel($package);
        $path = $this->trimPath($path);

        //$namespaces = $ontology->getParsedNamespaces();
        //$statement = $ontology->findFirstMatchingStatement(new Resource($path), OWL::EQUIVALENT_PROPERTY(), null);
        $statement = new Statement(new Resource("http://www.something/ont#testProperty"), new Resource("http://www.something/ont#testProperty"), new Resource("http://www.something/ont#testProperty"));
        if (!is_null($statement)) {
            $result = new stdClass ();
            $result->property = $statement->getObject();
            //$result->prefix = $namespaces[$statement->getObject()->getNamespace()];
            return $result;
        }

        return false;
    }

    /*
     * This function retrieves all triples containing mapping.
     * 
     * @param string $package The package to get mappings from
     * @return Mixed Returns array with triples or 
     */

    public function getMapping($package) {
        $ontology = $this->getModel($package);
        $classes = $ontology->find(null, OWL::EQUIVALENT_CLASS(), null);
        $properties = $ontology->find(null, OWL::EQUIVALENT_PROPERTY(), null);
        
        $result = array_merge($classes->triples,$properties->triples);
        
        $namespaces = $ontology->getParsedNamespaces();
        
        $mapping = array();

        foreach ($result as $triple) {
            $temp = new stdClass();
            $temp->map = $triple->getObject()->getURI();
            
            $namespace = $triple->getObject()->getNamespace();
            $temp->prefix = $namespaces[$namespace];
            $temp->nmsp = $namespace;
                        
            $mapping[$triple->getSubject()->getURI()] = $temp;
        }

        if (count($mapping) > 0)
            return $mapping;

        return false;
    }


    public function generateOntologyFromTabular($package, $resource, $fields) {
        $model = $this->getModel($package);
        
        $model->add(new Statement(new Resource($resource), RDF::TYPE(), TDTML::TDTRESOURCE()));

        $model->add(new Statement(new Resource($resource . '/stdClass'), RDF::TYPE(), OWL::OWL_CLASS()));

        foreach ($fields as $field) {
            $model->add(new Statement(new Resource($resource . '/stdClass/' . $field), RDF::TYPE(), RDF::PROPERTY()));
        }

    }

    //Private Methods

    /*
     * Function retrieving the unique URI for the package onthology
     */
    public function getOntologyURI($package) {
        return Config::$HOSTNAME . Config::$SUBDIR . 'Ontology/' . $package . '/';
    }

    private function getModel($package) {
        $store = RbModelFactory::getRbStore();
        //gets the model if it exist, else make a new one. Either way, it's the right one.
        //Gets a ResModel containing an RbModel, which doesn't store statements in memory, only in db.
        $model = RbModelFactory::getRbModel($store, $this->getOntologyURI($package));

        $resource = new Resource($this->getOntologyURI($package));
        $literal = new Literal("Ontology of the " . $package . " package in The DataTank", "en", "datatype:STRING");
        $model->add(new Statement($resource, RDF::TYPE(), OWL::ONTOLOGY()));
        $model->add(new Statement($resource, RDFS::COMMENT(), $literal));
        $model->setBaseUri($this->getOntologyURI($package));

        return $model;
    }

    private function isPathProperty($path) {
        $s = substr($path, strripos($path, '/') + 1);
        return lcfirst($s) === $s; //first letter is lowercase, so property
    }

    private function trimPath($path) {
        //We need to rewrite the resource url to add the right mapping
        //Find position of last slash
        $pos = strripos($path, '/');

        //Check if the url ends on a slash. If so, remove the slash en check position of last slash again.
        if ($pos == strlen($path) - 1)
            return substr($path, 0, strlen($path) - 1);

        return $path;
    }

}

?>