<?php
/**
 * This will get a resource description from the databank and add the right strategy to process the call to the GenericResource class
 *
 * @package The-Datatank/model
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert
 * @author Jan Vansteenlandt
 */

include_once("model/resources/AResource.class.php");
include_once("model/resources/GenericResource.class.php");

class GenericResourceFactory extends AResourceFactory {

    public function hasResource($package,$resource){
        $resource = DBQueries::hasGenericResource($package, $resource);
        return isset($resource["present"]) && $resource["present"] >= 1;   
    }

    public function createCreator($package,$resource, $parameters, $RESTparameters){
        include_once("model/resources/create/GenericResourceCreator.class.php");
        if(!isset($parameters["generic_type"])){
            throw new ResourceAdditionTDTException("generic type hasn't been set");
        }
        $creator = new GenericResourceCreator($package,$resource, $RESTparameters, $parameters["generic_type"]);
        foreach($parameters as $key => $value){
            $creator->setParameter($key,$value);
        }
        return $creator;
    }
    
    public function createReader($package,$resource, $parameters, $RESTparameters){
        include_once("model/resources/read/GenericResourceReader.class.php");
        $reader = new GenericResourceReader($package, $resource, $RESTparameters);
        $reader->processParameters($parameters);
        return $reader;
    }
        
    public function createDeleter($package,$resource, $RESTparameters){
        include_once("model/resources/delete/GenericResourceDeleter.class.php");
        $deleter = new GenericResourceDeleter($package,$resource, $RESTparameters);
        return $deleter;
    }

    // make Read doc
    public function makeDoc($doc){
        foreach($this->getAllResourceNames() as $package => $resourcenames){
            if(!isset($doc->$package)){
                $doc->$package = new StdClass();
            }
           
            foreach($resourcenames as $resourcename){
                $documentation = DBQueries::getGenericResourceDoc($package,$resourcename);
                $doc->$package->$resourcename = new StdClass();
                $doc->$package->$resourcename->documentation = $documentation["doc"];
                /**
                 * Create a generic resource, get the strategy and ask for 
                 * the read parameters of the strategy.
                 * NOTE: We don't ask for generic resource parameters, because there are none !
                 */
                $genres = new GenericResource($package,$resourcename);
                $strategy = $genres->getStrategy();

                $doc->$package->$resourcename->parameters = $strategy->documentReadParameters();
                $doc->$package->$resourcename->requiredparameters = array();
            }
        }
    }

    public function makeDescriptionDoc($doc){
        foreach($this->getAllResourceNames() as $package => $resourcenames){
            if(!isset($doc->$package)){
                $doc->$package = new StdClass();
            }
           
            foreach($resourcenames as $resourcename){
                $documentation = DBQueries::getGenericResourceDoc($package,$resourcename);
                $doc->$package->$resourcename = new StdClass();
                $doc->$package->$resourcename->documentation = $documentation["doc"];
                $doc->$package->$resourcename->generic_type = $documentation["type"];
                $doc->$package->$resourcename->resource_type = "generic";
                /**
                 * Get the strategy properties
                 */
                $genericId = $documentation["id"];
                $strategyTable = "generic_resource_". strtolower($documentation["type"]);
                
                $result = DBQueries::getStrategyProperties($genericId,$strategyTable);
                if(isset($result[0])){
                    foreach($result[0] as $column => $value){
                        if($column != "id" && $column != "gen_resource_id"){
                            $doc->$package->$resourcename->$column = $value;
                        }
                    }
                }

                /**
                 * Get the metadata properties
                 */
                $metadata = DBQueries::getMetaData($package,$resourcename);
                if(!empty($metadata)){
                    foreach($metadata as $name => $value){
                        if($name != "id" && $name != "resource_id"){
                            $doc->$package->$resourcename->$name = $value;
                        }
                    }
                }
                
                /**
                 * Get the published columns
                 */
                $columns = DBQueries::getPublishedColumns($genericId);
                // pretty formatted columns 
                $prettyColumns = array();
                if(!empty($columns)){
                    foreach($columns as $columnentry){
                        $prettyColumns[$columnentry["column_name"]] = $columnentry["column_name_alias"];
                    }
                    $doc->$package->$resourcename->columns = $prettyColumns;
                }
                
                $doc->$package->$resourcename->parameters = array();
                $doc->$package->$resourcename->requiredparameters = array();
            }
        }
    }

    protected function getAllResourceNames(){
        $results = DBQueries::getAllGenericResourceNames();
        $resources = array();
        foreach($results as $result){
            if(!array_key_exists($result["package_name"],$resources)){
        	    $resources[$result["package_name"]] = array();
            }
            $resources[$result["package_name"]][] = $result["res_name"];
        }
        return $resources;
    }


    public function makeDeleteDoc($doc){
        //add stuff to the delete attribute in doc. No other parameters expected
        $d = new StdClass();
        if(!isset($doc->delete)){
            $doc->delete = new StdClass();
        }        
        $d->doc = "You can delete every generic resource with a DELETE HTTP request on the definition in TDTInfo/Resources.";
        $doc->delete->generic = new StdClass();
        $doc->delete->generic = $d;
    }
    
    public function makeCreateDoc($doc){
        $d = array();
        foreach($this->getAllStrategies() as $strategy){
            include_once("model/resources/create/GenericResourceCreator.class.php");
            $res = new GenericResourceCreator("","", array(),$strategy);
            $d[$strategy] = new stdClass();
            $d[$strategy]->doc = "When your file is structured according to $strategy, you can perform a PUT request and load this file in this DataTank";
            $d[$strategy]->parameters = $res->documentParameters();
            $d[$strategy]->requiredparameters = $res->documentRequiredParameters();
        }
        if(!isset($doc->create)){
            $doc->create = new stdClass();
        }
        $doc->create->generic = new stdClass();
        $doc->create->generic = $d;
    }

    public function makeUpdateDoc($doc){
         $d = array();
        foreach($this->getAllStrategies() as $strategy){
            include_once("model/resources/update/GenericResourceUpdater.class.php");
            $res = new GenericResourceUpdater("","", array(),$strategy);
            $d[$strategy] = new stdClass();
            $d[$strategy]->doc = "When your generic resource is made you can update properties of it by passing the property names via a PATCH request to TDTAdmin/Resources. Note that not all properties are adjustable.";
            $d[$strategy]->parameters = array();
            $d[$strategy]->requiredparameters = array();
        }
        if(!isset($doc->update)){
            $doc->update = new stdClass();
        }
        $doc->update->generic = new stdClass();
        $doc->update->generic = $d;
    }

    private function getAllStrategies(){
        $strategies = array();
        if ($handle = opendir('custom/strategies')) {
            while (false !== ($strat = readdir($handle))) {
                //if the object read is a directory and the configuration methods file exists, then add it to the installed strategie
                if ($strat != "." && $strat != ".." && $strat != "README.md" && !is_dir("custom/strategies/" . $strat) && file_exists("custom/strategies/" . $strat)) {

                    include_once("custom/strategies/" . $strat);
                    $fileexplode = explode(".",$strat);
                    $class = new ReflectionClass($fileexplode[0]);
                    if(!$class->isAbstract()){
                        $strategies[] = $fileexplode[0];
                    }
                }
            }
            closedir($handle);
        }
        return $strategies;
    }
}

?>
