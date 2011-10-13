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

class GenericResourceFactory extends AResourceFactory {

    public function hasResource($package,$resource){
        $resource = DBQueries::hasGenericResource($package, $resource);
        return isset($resource["present"]) && $resource["present"] == 1;   
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
        return $reader;
    }
        
    public function createDeleter($package,$resource, $RESTparameters){
        include_once("model/resources/delete/GenericResourceDeleter.class.php");
        $deleter = new GenericResourceDeleter($package,$resource, $RESTparameters);
        return $deleter;
    }

    public function makeDoc($doc){        
        foreach($this->getAllResourceNames() as $package => $resourcenames){
            if(!isset($doc->$package)){
                $doc->$package = new StdClass();
                $doc->$package->creation_date = DBQueries::getPackageCreationTime($package);
            }
           
            foreach($resourcenames as $resourcename){
                $documentation = DBQueries::getGenericResourceDoc($package,$resourcename);
                $doc->$package->$resourcename = new StdClass();
                $doc->$package->$resourcename->doc = $documentation["doc"];
                $doc->$package->$resourcename->requiredparameters = array();
		$doc->$package->$resourcename->parameters = array();
                $doc->$package->$resourcename->creation_timestamp = (int)$documentation["creation_timestamp"];
                $doc->$package->$resourcename->modification_timestamp = (int)$documentation["last_update_timestamp"];
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
        foreach($this->getAllResourceNames() as $package => $v){
            foreach($v as $resource){
                $d = new stdClass();
                $d->doc = "Delete this generic resource by calling the URI given in this object with a HTTP DELETE method";
                $d->uri = Config::$HOSTNAME . Config::$SUBDIR . $package . "/" . $resource;
                $doc->delete[] = $d;
            }
        }
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

    private function getAllStrategies(){
        $strategies = array();
        //open the custom directory and loop through it
        /*if ($handle = opendir('custom/genericstrategies')) {
            while (false !== ($strat = readdir($handle))) {
                //if the object read is a directory and the configuration methods file exists, then add it to the installed strategies
                if ($strat != "." && $strat != ".." && file_exists("custom/genericstrategies/" . $strat)) {
                    include_once("custom/genericstrategies/" . $strat);
                    $fileexplode = explode(".",$strat);
                    $strategies[] = $fileexplode[0];
                }
            }
            closedir($handle);
            }*/
        //open the main strategies directory as well and loop through it
        if ($handle = opendir('model/resources/strategies')) {
            while (false !== ($strat = readdir($handle))) {
                //if the object read is a directory and the configuration methods file exists, then add it to the installed strategies
                if ($strat != "." && $strat != ".." && file_exists("model/resources/strategies/" . $strat)) {
                    include_once("model/resources/strategies/" . $strat);
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
