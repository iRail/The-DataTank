<?php

/**
 * This class RDFMapper maps resources from a package to RDF classes. It auto generates mapping schemes and handles modifications from the user,
 * 
 * @package The-Datatank/model/semantics
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Miel Vander Sande
 */
class RDFMapper {

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

        $tdtmlURI = "http://thedatatank.com/tdtml/1.0#";
        $rdfmodel->addNamespace("tdtml", $tdtmlURI);
        $rdfmodel->addNamespace("owl", OWL_NS);

        // Using the Resource-Centric method        
        // Create the resources
        $package_res = $rdfmodel->createResource("");

        $tdtpackage_res = $rdfmodel->createResource($tdtmlURI . "TDTPackage");
        $tdtresource_res = $rdfmodel->createResource($tdtmlURI . "TDTResource");
        $tdtproperty_res = $rdfmodel->createResource($tdtmlURI . "TDTProperty");

        //creating TDTML property resources
        $is_a_prop = $rdfmodel->createProperty($tdtmlURI . "is_a");
        $name_prop = $rdfmodel->createProperty($tdtmlURI . "name");
        $has_resources_prop = $rdfmodel->createProperty($tdtmlURI . "has_resources");
        $maps_prop = $rdfmodel->createProperty($tdtmlURI . "maps");

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
     * Adds a mapping between TDT Resource and a class.
     * The class is known internally or described in suplied onthology namespace. 
     *
     * @param	string $package
     * @param	string $tdt_resource
     * @param	string $class
     * @param	string $nmsp
     * @return	bool Returns true if mapping is added correctly.
     * @access	public
     */
    public function addMappingStatement($package, $resource, $class, $nmsp = null) {
        $rdfmodel = $this->getMapping($package);
        $rdfresource = $rdfmodel->createResource($resource);
        $property = $rdfmodel->createProperty($tdtmlURI . "maps");
        //adding the class to the mapping - UNDER CONSTRUCTION
        //Implement:
        // - namespace+classname as $class
        // - short:classname
        // - namespace,classname seperate
        // - short:classname, namespace both available
        // Add a new namespace to the model!! 
        $object;
        if (is_null($nmsp))
            $object = $this->browseInternalVocabulary ($class);
        else
            $object = $rdfmodel->createResource($nmsp . $class);
        
        $rdfresource->addProperty($property, $object);
        
    }
    
    /**
     * Looks for an existing vocabulary under rdfapi-php/api/vocabulary
     *
     * @param	string $class
     * @return	ResResource
     * @access	public
     */
    private function browseInternalVocabulary($class) {
        
    }

    /**
     * Removes a mapping between TDT Resource and a class.
     *
     * @param	string $package
     * @param	string $tdt_resource
     * @return	bool Returns true if mapping is renovedcorrectly.
     * @access	public
     */
    public function removeMappingStatement($package, $resource) {
        
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