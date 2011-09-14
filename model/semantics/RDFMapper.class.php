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

    private $package;

    function __construct($package) {
        $this->package = $package;
    }

    /**
     * @return an string containing a possible RDF mapping in D2RQ-ML for a specific package.  
     */
    public function suggestMapping() {

        $model = ResourcesModel::getInstance();
        $mapping = "";

        $allresources = $model->getAllResourceNames();

        //limit the rources to the required package
        $allresources = $allresources[$this->package];

        $rdfmodel = $this->createRdfModel();

        $foafModel = new FOAF_RES();

        $tdtmlURI = "http://thedatatank.com/tdtml/1.0#";
        $mapURI = Config::$HOSTNAME . Config::$SUBDIR;

        $rdfmodel->setBaseURI($mapURI);

        $rdfmodel->addNamespace("tdtml", $tdtmlURI);
        $rdfmodel->addNamespace("foaf", FOAF_NS);

        // Using the Resource-Centric method        
        // Create the resources
        $package_res = $rdfmodel->createResource($this->package);

        $tdtpackage_res = $rdfmodel->createResource($tdtmlURI . "TDTPackage");
        $tdtresource_res = $rdfmodel->createResource($tdtmlURI . "TDTResource");
        $tdtproperty_res = $rdfmodel->createResource($tdtmlURI . "TDTProperty");

        //creating TDTML property resources
        $is_a_prop = $rdfmodel->createProperty($tdtmlURI . "is_a");
        $name_prop = $rdfmodel->createProperty($tdtmlURI . "name");
        $has_resources_prop = $rdfmodel->createProperty($tdtmlURI . "has_resources");
        $maps_prop = $rdfmodel->createProperty($tdtmlURI . "maps");

        //Creating literal
        $package_name_lit = $rdfmodel->createTypedLiteral($this->package, "datatype:STRING");

        // Add the properties
        $package_res->addProperty($is_a_prop, $tdtpackage_res);
        $package_res->addProperty($name_prop, $package_name_lit);


        $resources_bag = $rdfmodel->createBag();
        $package_res->addProperty($has_resources_prop, $resources_bag);
        /* Using the Statement-Centric method -- less useful for this project
          $packageResource = new Resource($mapURI, $this->package);
          $rdfmodel->add(new Statement($packageResource, new Resource($tdtmlURI, "is_a"), new Resource($tdtmlURI, "Package")));
          $rdfmodel->add(new Statement($packageResource, new Resource($tdtmlURI, "package_name"), new Literal($this->package, "en", "STRING")));
         */

        foreach ($allresources as $resource) {
            $resource_res = $rdfmodel->createResource($resource);
            $resources_bag->add($resource_res);

            $resource_name_lit = $rdfmodel->createTypedLiteral($resource, "datatype:STRING");
            $resource_res->addProperty($name_prop, $resource_name_lit);
            $resource_res->addProperty($is_a_prop, $tdtresource_res);
            $resource_res->addProperty($maps_prop, OWL_RES::OWL_CLASS());

            //echo $model->

            /* $resourceResource = new Resource($mapURI, $this->package . "/" . $resource);
              $rdfmodel->add(new Statement($packageResource, new Resource($tdtmlURI, "has_resource"), $resourceResource));
              $rdfmodel->add(new Statement($resourceResource, new Resource($tdtmlURI, "is_a"), new Resource($tdtmlURI, "Resource")));
              $rdfmodel->add(new Statement($resourceResource, new Resource($tdtmlURI, "maps"), FOAF::PERSON()));
             */
        }

        return $rdfmodel;
    }

    private function createRdfModel() {
        //Future: dbmodel: automatic storing of rdl model in DB. Find out compatibility with required RDBMS systems first.

        $db_values = explode(";", Config::$DB);
        $db_host = explode("=", $db_values[0]);
        $db_host = $db_host[1];
        $db_name = explode("=", $db_values[0]);
        $db_name = $db_name[1];
        
        $modelfactory = new ModelFactory();

        //$mysql_database = $modelfactory->getDbStore('MySQL', $db_host, $db_name, Config::$DB_USER, Config::$DB_PASSWORD);
        //$mysql_database->createTables('MySQL');

        

        //Return MemModel
        //return $modelfactory->getDefaultModel();

        return $modelfactory->getResModel(MEMMODEL);
    }

}

?>
