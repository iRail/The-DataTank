<?php
/**
 * This will proxy the updater to a generic strategy resource
 * 
 * @package The-Datatank/model/resources/update
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert
 */
include_once("AUpdater.class.php");

class GenericResourceUpdater extends AUpdater {

    private $strategy;

    public function __construct($package, $resource, $RESTparameters) {
        parent::__construct($package, $resource, $RESTparameters);
        //create an instance of this strategy
        $result = DBQueries::getGenericResourceType($package, $resource);
        $this->strategyname = $result["type"];
        include_once("custom/strategies/" . $this->strategyname . ".class.php");
        $this->strategy = new $this->strategyname();
    }

    public function getParameters(){
        return $this->strategy->getUpdateParameters();
    }

    public function getRequiredParameters() {
        return $this->strategy->getRequiredUpdateParameters();
    }

    protected function setParameter($key, $value) {
        $this->strategy->$key = $value;
    }

    public function update() {
        $this->strategy->onUpdate($this->package, $this->resource);
    }

    public function getDocumentation() {
        return "Do an update on a generic resource, depending on what the strategy has specified.";
    }

}
?>