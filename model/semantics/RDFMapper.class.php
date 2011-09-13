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

        $model = $this->createModel();

        $foafModel = new FOAF_RES();

        $tdtmlURI = "http://thedatatank.org/tdtml/1.0#";
        $mapURI = Config::$HOSTNAME . Config::$SUBDIR;

        $model->setBaseURI($mapURI);

        $model->addNamespace("tdtml", $tdtmlURI);
        $model->addNamespace("map", $mapURI);
        $model->addNamespace("foaf", FOAF_NS);



        // Using the Resource-Centric method        
        // Create the resources
        $package_res = $model->createResource($this->package);

        $tdtpackage_res = $model->createResource($tdtmlURI . "TDTPackage");
        $tdtresource_res = $model->createResource($tdtmlURI . "TDTResource");
        $tdtproperty_res = $model->createResource($tdtmlURI . "TDTProperty");

        //creating TDTML property resources
        $is_a_prop = $model->createProperty($tdtmlURI . "is_a");
        $name_prop = $model->createProperty($tdtmlURI . "name");
        $has_resources_prop = $model->createProperty($tdtmlURI . "has_resources");
        $maps_prop = $model->createProperty($tdtmlURI . "maps");

        //Creating literal
        $package_name_lit = $model->createTypedLiteral($this->package, "datatype:STRING");

        // Add the properties
        $package_res->addProperty($is_a_prop, $tdtpackage_res);
        $package_res->addProperty($name_prop, $package_name_lit);


        $resources_bag = $model->createBag();
        $package_res->addProperty($has_resources_prop, $resources_bag);
        /* Using the Statement-Centric method -- less useful for this project
          $packageResource = new Resource($mapURI, $this->package);
          $model->add(new Statement($packageResource, new Resource($tdtmlURI, "is_a"), new Resource($tdtmlURI, "Package")));
          $model->add(new Statement($packageResource, new Resource($tdtmlURI, "package_name"), new Literal($this->package, "en", "STRING")));
         */



        foreach ($allresources as $resource) {
            $resource_res = $model->createResource($resource);
            $resources_bag->add($resource_res);

            $resource_name_lit = $model->createTypedLiteral($resource, "datatype:STRING");
            $resource_res->addProperty($name_prop, $resource_name_lit);
            $resource_res->addProperty($is_a_prop, $tdtresource_res);
            $resource_res->addProperty($maps_prop, OWL_RES::OWL_CLASS());

            /* $resourceResource = new Resource($mapURI, $this->package . "/" . $resource);
              $model->add(new Statement($packageResource, new Resource($tdtmlURI, "has_resource"), $resourceResource));
              $model->add(new Statement($resourceResource, new Resource($tdtmlURI, "is_a"), new Resource($tdtmlURI, "Resource")));
              $model->add(new Statement($resourceResource, new Resource($tdtmlURI, "maps"), FOAF::PERSON()));
             */
        }


        $mapping = $model->writeRdfToString();

        $model->getModel()->close();

        return $mapping;
    }

    private function createModel() {
        //Future: dbmodel: automatic storing of rdl model in DB. Find out compatibility with required RDBMS systems first.
        /*
          $db_values = explode(";", Config::$DB);
          $db_host = explode("=", $db_values[0]);
          $db_host = $db_host[1];
          $db_name = explode("=", $db_values[0]);
          $db_name = $db_name[1];

          $mysql_database = ModelFactory::getDbStore('MySQL', $db_host, $db_name, Config::$DB_USER ,Config::$DB_PASSWORD );
          $mysql_database->createTables('MySQL');

          $modelURI = Config::$HOSTNAME.  Config::$SUBDIR. $this->package."_db";

          $dbmodel;

          if ($access_database->modelExists($modelURI))
          echo "WARNING! DbModel with the same URI: '$modelURI' already exists";
          else
          $dbmodel = $access_database->getNewModel($modelURI);

          return dbmodel;

         */

        $modelfactory = new ModelFactory();

        //Return MemModel
        //return $modelfactory->getDefaultModel();

        return $modelfactory->getResModel(MEMMODEL);
    }

}

?>
