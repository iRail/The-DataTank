<?php
/**
 * The abstract class for a factory: check documentation on the Factory Method Pattern if you don't understand this code.
 *
 * @package The-Datatank/resources
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert
 */

include_once("custom/strategies/AResourceStrategy.class.php");
include_once("model/resources/AResource.class.php");

class GenericResource{
    
    private $package;
    private $resource;
    private $strategyname;
    private $strategy;
    
    public function __construct($package,$resource){
        $this->package = $package;
        $this->resource = $resource;
        $result = DBQueries::getGenericResourceType($package, $resource);
        $this->strategyname = $result["type"];
    }

    public function getStrategy(){
        if(is_null($this->strategy)){
            include_once("custom/strategies/" . $this->strategyname . ".class.php");
            $this->strategy = new $this->strategyname();
        }
        return $this->strategy;
    }    

    public function readNonPaged(){
        $strat = $this->getStrategy();
        return $strat->readNonPaged($this->package,$this->resource);
    }

    public function readPaged($page){
        $strat = $this->getStrategy();
        return $strat->readPaged($this->package,$this->resource,$page);
    }
}

?>