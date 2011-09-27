<?php

/**
 * This class RDFMapper maps resources from a package to RDF classes. It auto generates mapping schemes and handles modifications from the user,
 *
 * @package The-Datatank/model/semantics
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Miel Vander Sande
 */
include_once('RDFConstants.php');

class RDFMapper {

    private $models;

    function __construct() {
        $this->models = array();
    }

    /**
     * Method called for updating the mapping model. This method responds to a POST request on a specific resource.
     *
     * @param	string $tdt_package The name of the package containing the resource
     * @param	string $tdt_resource The path of the resource
     * @param   array   $content Array containing POST variables
     * @return	ResModel
     * @access	public
     */
    public function update($tdt_package, $tdt_resource, $content) {

        if (isset($content['rdf_mapping_method'])) {
            //If rdf_mapping_bash is set, user wants to map multiple related resources instead of only this resource
            if (isset($content['rdf_mapping_bash'])) {
                //Call method stripResourcePath to get the base url
                $tdt_resource = $this->stripResourcePath($tdt_resource);

                //Append new indexes or * to the resource path
                if ($content['rdf_mapping_bash'] == '*') {
                    $this->executeMethod($tdt_package, $tdt_resource . '*', $content);
                } else if (is_numeric($content['rdf_mapping_bash'])) {
                    //Just one index is specified
                    $this->executeMethod($tdt_package, $tdt_resource . $content['rdf_mapping_bash'], $content);
                } else if (strpos($content['rdf_mapping_bash'], ',')) {
                    //Multiple indexes are specified
                    $indexes = explode(',', $content['rdf_mapping_bash']);
                    foreach ($indexes as $value) {
                        $this->executeMethod($tdt_package, $tdt_resource . $value, $content);
                    }
                } else {
                    throw new RdfTDTException('Value of rdf_mapping_bash is not correct');
                }
            } else {
                $this->executeMethod($tdt_package, $tdt_resource, $content);
            }
        } else {
            throw new RdfTDTException('Mapping method not specified');
        }
    }

    /**
     * Removes the last rest parameter
     *
     * @param string $tdt_resource URI of the resource
     * @return string URI without trailing slash
     */
    private function stripResourcePath($tdt_resource) {
        //We need to rewrite the resource url to add the right mapping
        //Find position of last slash
        $pos = strripos($tdt_resource, '/');

        //Check if the url ends on a slash. If so, remove the slash en check position of last slash again.
        if ($pos == strlen($tdt_resource) - 1)
            $pos = strripos(substr($tdt_resource, 0, strlen($tdt_resource) - 1), '/');

        //remove everything behind the slash to get base URI and return
        return substr($tdt_resource, 0, $pos + 1);
    }

    /**
     * Executes the right operation on the mapping model, specified in .
     *
     * @param	string $tdt_package The name of the package containing the resource
     * @param	string $tdt_resource The path of the resource
     * @param   array   $content Array containing POST variables
     * @return	ResModel
     * @access	private
     */
    private function executeMethod($tdt_package, $tdt_resource, $content) {
        switch ($content['rdf_mapping_method']) {
            case RDFConstants::$MAPPING_UPDATE :
                if (isset($content['rdf_mapping_nmsp']))
                    $this->addMappingStatement($tdt_package, $tdt_resource, $content['rdf_mapping_class'], $content['rdf_mapping_nmsp']);
                else
                    $this->addMappingStatement($tdt_package, $tdt_resource, $content['rdf_mapping_class']);
                break;
            case RDFConstants::$MAPPING_DELETE :
                $this->removeMappingStatement($tdt_package, $tdt_resource);
                break;
            case RDFConstants::$MAPPING_EQUALS :
                $this->equalMappingStatement($tdt_package, $tdt_resource, $content['rdf_mapping_class']);
                break;
            default:
                throw new RdfTDTException('Mapping method does not exist');
        }
    }

    /**
     * Gets the current URI of the mapping model
     *
     * @param string $tdt_package
     * @return string
     */
    private function getMappingURI($tdt_package) {
        return $mapURI = Config::$HOSTNAME . Config::$SUBDIR . $tdt_package . '/';
    }

    /**
     * Method called for updating the mapping model. This method responds to a POST request on a specific resource.
     *
     * @param	ResModel $model The model containing the mapping
     * @param	string $tdt_resource The path of the resource
     * @param   array   $content Array containing POST variables
     * @return	ResModel
     * @access	public
     */
    private function buildNewMapping(&$model, $tdt_package) {

        $model->addNamespace("tdtml", RDFConstants::$TDML_NS);
        $model->addNamespace("owl", OWL_NS);

        // Using the Resource-Centric method
        // Create the resources
        $package_res = $model->createResource($this->getMappingURI($tdt_package));

        $tdtpackage_res = $model->createResource(RDFConstants::$TDML_NS . "TDTPackage");
        $tdtresource_res = $model->createResource(RDFConstants::$TDML_NS . "TDTResource");
        $tdtproperty_res = $model->createResource(RDFConstants::$TDML_NS . "TDTProperty");

        //creating TDTML property resources
        $name_prop = $model->createProperty(RDFConstants::$TDML_NS . "name");
        $has_resources_prop = $model->createProperty(RDFConstants::$TDML_NS . "has_resources");
        $maps_prop = $model->createProperty(RDFConstants::$TDML_NS . "maps");

        //Creating literal
        $package_name_lit = $model->createTypedLiteral($tdt_package, "datatype:STRING");

        // Add the properties
        $package_res->addProperty(RDF_RES::TYPE(), $tdtpackage_res);
        $package_res->addProperty($name_prop, $package_name_lit);


        $resources_list = $model->createList();
        $package_res->addProperty($has_resources_prop, $resources_list);
        $resource_res->addProperty($maps_prop, RDF_RES::DESCRIPTION());

        //Get all resources in package
        $doc = ResourcesModel::getInstance()->getAllDoc();
        //limit the rources to the required package

        $allresources = get_object_vars($doc);

        foreach ($allresources[$tdt_package] as $resource => $val) {

            $resource_res = $model->createResource($this->getMappingURI($tdt_package) . $resource);
            $resources_list->add($resource_res);

            $resource_name_lit = $model->createTypedLiteral($resource, "datatype:STRING");
            $resource_res->addProperty($name_prop, $resource_name_lit);
            $resource_res->addProperty(RDF_RES::TYPE(), $tdtresource_res);
            $resource_res->addProperty($maps_prop, RDF_RES::DESCRIPTION());
        }
    }

    /**
     * Gets the ResModel object containing the mapping file for a package.
     *
     * @param	string $tdt_package The name of the package
     * @return	ResModel
     * @access	public
     */
    public function getMapping($tdt_package) {
        //keep a model for a package in the memory
        //Might be changed later if it is too memory consuming
        if (array_key_exists($tdt_package, $this->models))
            return $this->models[$tdt_package];

        $store = RbModelFactory::getRbStore();
        //gets the model if it exist, else make a new one. Either way, it's the right one.
        $model = RbModelFactory::getResModel(RBMODEL, $this->getMappingURI($tdt_package));
        //If the model has no statements, give it a basic mapping structure
        if ($model->isEmpty())
            $this->buildNewMapping($model, $tdt_package);

        $this->models[$tdt_package] = $model;

        return $model;
    }

    /**
     * Adds a mapping between TDT Resource and a resource. If a mapping already exists for this resource, it gets updated.
     * The class is known internally or described in suplied onthology namespace.
     *
     * @param	string $tdt_package The name of the package containing the resource
     * @param	string $tdt_resource The path of the resource
     * @param	string $class The name of the class to map the resource to.
     * @param	string $nmsp OPTIONAL The namespace where the class belongs to.
     * @access	private
     */
    private function addMappingStatement($tdt_package, $tdt_resource, $class, $nmsp = null) {
        if (!(isset($tdt_package) && isset($tdt_resource) && isset($class)))
            throw new RdfTDTException('Package, Resource or Mapping class unknown ');

        $model = $this->getMapping($tdt_package);

        $resource = $model->createResource($tdt_resource);
        $property = $model->createProperty(RDFConstants::$TDML_NS . "maps");

        //adding the class to the mapping
        //Implements:
        // - namespace+classname as $class
        // - prefix:classname only if prefix of internally known library
        // - namespace,classname seperate
        // - short:classname, namespace both available
        // Add a new namespace to the model!!
        $prefix = '';

        if (stripos($class, ":")) {
            $explode = explode(":", $class);
            $prefix = $explode[0];
            $class = $explode[1];
        }

        //retrieve the right namespace and class
        if (is_null($nmsp)) {
            if (!(array_key_exists($prefix, RDFConstants::$VOCABULARIES)))
                throw new RdfTDTException('Namespace ' . $prefix . ' is unknown. Specify namespace URI in rdf_mapping_nmsp POST variable.');
            $nmsp = RDFConstants::$VOCABULARIES[$prefix];
        } else {
            if ($prefix == '')
                $prefix = array_search($nmsp, RDFConstants::$VOCABULARIES);
        }
        $object = new ResResource($nmsp . $class);

        //add the namespace to the model
        $model->addNamespace($prefix, $nmsp);

        //Does this resource already have a mapping?
        if ($resource->hasProperty($property)) {
            //remove existing mapping first
            $resource->removeAll($property);
        }
        $resource->addProperty($property, $object);
    }

    /**
     * Maps a TDT resource to another, but equal TDT resource.
     *
     * @param	string $tdt_package The name of the package containing the resource
     * @param	string $tdt_resource The path of the resource
     * @return	bool Returns true if mapping is renovedcorrectly.
     * @access	private
     */
    private function equalMappingStatement($tdt_package, $tdt_resource, $equal_resource) {
        if (!(isset($tdt_package) && isset($tdt_resource) && isset($equal_resource)))
            throw new RdfTDTException('Package, Resource or Equal resource unknown ');

        $model = $this->getMapping($tdt_package);

        $resource = $model->createResource($this->getMappingURI($tdt_package) . $tdt_resource);
        $object = $model->createResource($equal_resource);
        $equals_prop = $model->createProperty(OWL_RES::SAME_AS());
        $maps_prop = $model->createProperty(RDFConstants::$TDML_NS . "maps");

        $resource->addProperty($equals_prop, $object);
        $object->addProperty($equals_prop, $resource);

        //$map = $object->getProperty($maps_prop);
        //$resource->addProperty($maps_prop, $map);
    }

    /**
     * Removes a mapping between TDT Resource and a resource.
     *
     * @param	string $tdt_package The name of the package containing the resource
     * @param	string $tdt_resource The path of the resource
     * @return	bool Returns true if mapping is removed correctly.
     * @access	private
     */
    private function removeMappingStatement($tdt_package, $tdt_resource) {
        if (!(isset($tdt_package) && isset($tdt_resource)))
            throw new RdfTDTException('Package or Resource unknown ');

        $model = $this->getMapping($tdt_package);

        $subject = $model->createResource($this->getMappingURI($tdt_package) . $tdt_resource);
        $statements = $model->find($subject, RDFConstants::$TDML_NS . 'maps', null);

        foreach ($statements as $statement) {
            if (!$model->remove($statement))
                throw new RdfTDTException('Mapping statement of ' . $statement->getSubject()->toString() . ' was not deleted');
        }
    }

    /**
     * Returns a ResResource containing the mapped resource of a TDT resource.
     *
     * @param	string $tdt_package The name of the package containing the resource
     * @param	string $tdt_resource The path of the resource
     * @return	ResResoure
     * @access	public
     */
    public function getResourceMapping($tdt_package, $tdt_resource) {
        if (!(isset($tdt_package) && isset($tdt_resource)))
            throw new RdfTDTException('Package or Resource unknown ');

        //Retrieve mapping model from db.
        $model = $this->getMapping($tdt_package);
        $mapping = $this->lookupMapping($model, $tdt_resource);

        //If no mapping is found, check if there is a generic mapping
        if (is_null($mapping)) {
            //rewrite URI to generic
            $tdt_resource = $this->stripResourcePath($tdt_resource) . '*';
            $mapping = $this->lookupMapping($model, $tdt_resource);
        }

        if (is_null($mapping))
            return null;
        else
        //Object of the triple is the needed class
            return $mapping->getObject();
    }

    /**
     * Look up resource that is mapped to this resource
     *
     * @param ResModel $model
     * @param string $tdt_resource
     * @return array Array of Statement objects containing equal resources
     */
    private function lookupMapping($model, $tdt_resource) {
        $resource_res = $model->createResource($tdt_resource);
        $maps_prop = $model->createProperty(RDFConstants::$TDML_NS . "maps");
        //Looks for the first triple statement where the resource has the TDML maps property.
        $mapping = $model->findFirstMatchingStatement($resource_res, $maps_prop, null);

        return $mapping;
    }

    /**
     * Look up resources that are equal to this resource
     *
     * @param ResModel $model
     * @param string $tdt_resource
     * @return array Array of Statement objects containing equal resources
     */
    private function lookupEquals($model, $tdt_resource) {
        $resource_res = $model->createResource($tdt_resource);
        $equals_prop = $model->createProperty(RDFConstants::$TDML_NS . "equals");
        return $resource_res->listProperties($equals_prop);
    }

}

?>