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

    /**
     * Gets the ResModel object containing the mapping file for a package.
     *
     * @param	string $package
     * @return	ResModel
     * @access	public
     */
    public function update($package, $resource, $content) {

        if (isset($content['rdf_mapping_method'])) {
            switch ($content['rdf_mapping_method']) {
                case RDFConstants::$MAPPING_UPDATE :
                    if (isset($content['rdf_mapping_nmsp']))
                        $this->addMappingStatement($package, $resource, $content['rdf_mapping_class'], $content['rdf_mapping_nmsp']);
                    else
                        $this->addMappingStatement($package, $resource, $content['rdf_mapping_class']);
                    break;
                case RDFConstants::$MAPPING_DELETE :
                    $this->removeMappingStatement($package, $resource);
                    break;
                case RDFConstants::$MAPPING_EQUALS :
                    $this->equalMappingStatement($package, $resource, $content['rdf_mapping_class']);
                    break;
                default:
                    throw new RdfTDTException('Mapping method does not exist');
            }
        }else {
            throw new RdfTDTException('Mapping method not specified');
        }
    }

    private function getMappingURI($package) {
        return $mapURI = Config::$HOSTNAME . Config::$SUBDIR . $package . '/';
    }

    private function buildNewMapping($package) {

        $model = ResourcesModel::getInstance();
        $mapping = "";

        $allresources = $model->getAllResourceNames();

        //limit the rources to the required package
        $allresources = $allresources[$package];

        $rdfmodel = $this->createRdfModel($package);

        $rdfmodel->addNamespace("tdtml", RDFConstants::$TDML_NS);
        $rdfmodel->addNamespace("owl", OWL_NS);

        // Using the Resource-Centric method        
        // Create the resources
        $package_res = $rdfmodel->createResource("");

        $tdtpackage_res = $rdfmodel->createResource(RDFConstants::$TDML_NS . "TDTPackage");
        $tdtresource_res = $rdfmodel->createResource(RDFConstants::$TDML_NS . "TDTResource");
        $tdtproperty_res = $rdfmodel->createResource(RDFConstants::$TDML_NS . "TDTProperty");

        //creating TDTML property resources
        $is_a_prop = $rdfmodel->createProperty(RDFConstants::$TDML_NS . "is_a");
        $name_prop = $rdfmodel->createProperty(RDFConstants::$TDML_NS . "name");
        $has_resources_prop = $rdfmodel->createProperty(RDFConstants::$TDML_NS . "has_resources");
        $maps_prop = $rdfmodel->createProperty(RDFConstants::$TDML_NS . "maps");

        //Creating literal
        $package_name_lit = $rdfmodel->createTypedLiteral($package, "datatype:STRING");

        // Add the properties
        $package_res->addProperty($is_a_prop, $tdtpackage_res);
        $package_res->addProperty($name_prop, $package_name_lit);


        $resources_bag = $rdfmodel->createBag();
        $package_res->addProperty($has_resources_prop, $resources_bag);

        foreach ($allresources as $resource) {
            $resource_res = $rdfmodel->createResource($resource);
            $resources_bag->add($resource_res);

            $resource_name_lit = $rdfmodel->createTypedLiteral($resource, "datatype:STRING");
            $resource_res->addProperty($name_prop, $resource_name_lit);
            $resource_res->addProperty($is_a_prop, $tdtresource_res);
            $resource_res->addProperty($maps_prop, OWL_RES::OWL_CLASS());
        }

        return $rdfmodel;
    }

    /**
     * Gets the ResModel object containing the mapping file for a package.
     *
     * @param	string $package
     * @return	ResModel
     * @access	public
     */
    public function getMapping($package) {
        $store = RbModelFactory::getRbStore();

        //check if the model alredy exists in database
        if ($store->modelExists($this->getMappingURI($package))) {
            return RbModelFactory::getResModel(RBMODEL, $this->getMappingURI($package));
        } else {
            //if not make a new one and fill in basic structure.
            return $this->buildNewMapping($package);
        }
    }

    /**
     * Adds a mapping between TDT Resource and a class. If a mapping already exists for this resource, it gets updated.
     * The class is known internally or described in suplied onthology namespace. 
     *
     * @param	string $tdt_package
     * @param	string $tdt_resource
     * @param	string $class
     * @param	string $nmsp
     * @return	bool Returns true if mapping is added correctly.
     * @access	public
     */
    public function addMappingStatement($tdt_package, $tdt_resource, $class, $nmsp = null) {
        if (!(isset($tdt_package) && isset($tdt_resource) && isset($class)))
            throw new RdfTDTException('Package, Resource or Mapping class unknown ');

        $model = $this->getMapping($tdt_package);
        $resource = $model->createResource($tdt_resource);
        $property = $model->createProperty(RDFConstants::$TDML_NS . "maps");

        echo $tdt_resource;


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
     * Maps a resource to another, but equal resource.
     *
     * @param	string $package
     * @param	string $tdt_resource
     * @return	bool Returns true if mapping is renovedcorrectly.
     * @access	public
     */
    private function equalMappingStatement($tdt_package, $tdt_resource, $equal_resource) {
        if (!(isset($tdt_package) && isset($tdt_resource) && isset($equal_resource)))
            throw new RdfTDTException('Package, Resource or Equal resource unknown ');

        $model = $this->getMapping($tdt_package);
        $resource = $model->createResource($tdt_resource);
        $object = $model->createResource($equal_resource);

        $resource->addProperty(OWL_RES::SAME_AS(), $object);
    }

    /**
     * Removes a mapping between TDT Resource and a class.
     *
     * @param	string $package
     * @param	string $tdt_resource
     * @return	bool Returns true if mapping is renovedcorrectly.
     * @access	public
     */
    public function removeMappingStatement($tdt_package, $tdt_resource) {
        if (!(isset($tdt_package) && isset($tdt_resource)))
            throw new RdfTDTException('Package or Resource unknown ');

        $model = $this->getMapping($tdt_package);
        $subject = $model->createResource($tdt_resource);
        $statements = $model->find($subject, RDFConstants::$TDML_NS . 'maps', null);

        foreach ($statements as $statement) {
            if (!$model->remove($statement)) {
                throw new RdfTDTException('Mapping statement of ' . $statement->getSubject()->toString() . ' was not deleted');
            }
        }
    }

    /**
     * Creates a new ResModel, containing RbModel, for handling and storing the mapping in tdml.
     *
     * @param	string $package
     * @return	ResModel
     * @access	public
     */
    private function createRdfModel($package) {
        $rdfmodel = RbModelFactory::getResModel(RBMODEL, $this->getMappingURI($package));
        $rdfmodel->setBaseURI($this->getMappingURI($package));
        return $rdfmodel;
    }

}

?>