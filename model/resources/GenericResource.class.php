<?php
/**
 * The abstract class for a factory: check documentation on the Factory Method Pattern if you don't understand this code.
 *
 * @package The-Datatank/resources
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert
 */

include_once("model/resources/strategies/AResourceStrategy.class.php");
include_once("model/resources/AResource.class.php");

class GenericResource extends AResource {
    private $strategyname;
    private $strategy; //this contains the right strategy to handle the call
    
    private $package;
    private $resource;
    
    public function __construct($package,$resource){
        $this->package = $package;
        $this->resource = $resource;
        $result = DBQueries::getGenericResourceType();
        $this->strategyname = $result["type"];
    }

    public function getStrategy(){
        if(is_null($this->strategy)){
            include_once("model/resources/strategies/" . $this->strategyname . ".class.php");
            $this->strategy = new $this->strategyname();
        }
        return $this->strategy;
    }    

    public function call(){
        $strat = $this->getStrategy();
        return $strat->onCall($this->package,$this->resource);
    }

    public function setParameter($name,$val){
        $this->strategy->$name = $val;
    }
    
    /* TO BE REMOVED
    public function getAllowedPrintMethods(){
        $results = DBQueries::getGenericResourcePrintMethods($this->package, $this->resource);
        return isset($results["print_methods"])?explode(";", $results["print_methods"]):array();
    }*/
    
}

?>