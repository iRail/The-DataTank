<?php

/**
 * Class for reading(fetching) a generic resource
 *
 * @package The-Datatank/model/resources/read
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */
include_once("AReader.class.php");
include_once("model/DBQueries.class.php");
include_once("model/resources/GenericResource.class.php");

class GenericResourceReader extends AReader {

    private $genres;

    public function __construct($package, $resource, $RESTparameters) {
        parent::__construct($package, $resource, $RESTparameters);
        $this->genres = new GenericResource($this->package, $this->resource);
        $strategy = $this->genres->getStrategy();
        $this->parameters = array_merge($this->parameters, $strategy->documentReadParameters());
        $this->getOntology();
    }
<<<<<<< HEAD
    

    protected function isPagedResource(){
        $result = DBQueries::getIsPaged($this->package,$this->resource);
        return $result["is_paged"];
    }

    /**
     * read method
     */
    public function readNonPaged(){
        return $this->genres->readNonPaged();
    }
=======
>>>>>>> 5f8aa9250821278a5eadac87ddee0377f516c7e6

    /**
     * read paged method
     * (same as read method, disguishment between paged and non paged is only 
     *  concrete in a strategy for generic resources.)
     */
<<<<<<< HEAD
    public function readPaged(){
        return $this->genres->readPaged($this->page);
=======
    public function read() {
        return $this->genres->call();
>>>>>>> 5f8aa9250821278a5eadac87ddee0377f516c7e6
    }

    /**
     * get the documentation about getting of a resource
     */
    public function getReadDocumentation() {
        $result = DBQueries::getGenericResourceDoc($this->package, $this->resource);
        return isset($result["doc"]) ? $result["doc"] : "";
    }

    /**
     * A generic resource doesn't have parameters yet, strategies can however
     */
<<<<<<< HEAD
    public function setParameter($key,$value){
        if($key == "page"){
            $this->$key = $value;
        }else{ // it's a strategy parameter
            /**
             * pass along the parameters to the strategy
             */
            $strategy = $this->genres->getStrategy();
            $strategy->setParameter($key,$value);
        }
        
=======
    public function setParameter($key, $value) {
        /**
         * pass along the parameters to the strategy
         */
        $strategy = $this->genres->getStrategy();
        $strategy->setParameter($key, $value);
>>>>>>> 5f8aa9250821278a5eadac87ddee0377f516c7e6
    }

    protected function getOntology() {
        if (!OntologyProcessor::getInstance()->hasOntology($this->package)) {
            $strategy = $this->genres->getStrategy();
            $fields = $strategy->getFields($this->package, $this->resource);
            OntologyProcessor::getInstance()->generateOntologyFromTabular($this->package, $this->resource, $fields);
        }
    }

}

?>