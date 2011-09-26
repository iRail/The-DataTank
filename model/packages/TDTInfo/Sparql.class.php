<?php
/**
 * This class respresents a sparql endpoint.
 *
 * @package The-Datatank/packages/TDTInfo
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Miel Vander Sande
 */
class Sparql extends AReader {

    private $result;

    public static function getParameters() {

    }

    public static function getRequiredParameters() {
    }

    public function read() {
        $this->getData();
        return $this->result;
    }

    private function getData() {
        $store = RbModelFactory::getRbStore();
        $getParam = RequestURI::getInstance()->getGET();
        
        $result = $store->sparqlQuery($getParam['query']);
    }

    public static function getDoc() {
        return "This is the SPARQL endpoint";
    }

}

?>
