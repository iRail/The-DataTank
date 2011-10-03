<?php

/**
 * This class gives access to the onthology of resources
 *
 * @package The-Datatank/packages/TDTInfo
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Miel Vander Sande
 */
class Onthology extends AReader{

    private $mapping;

    public function __construct($package,$resource){
        parent::__construct($package,$resource);
    }
    
    public static function getParameters() {
        return array("package" => "Name of a package that needs to be analysed, must be set !",
            "resource" => "Name of a resource within the given package, is not required.",
        );
    }

    public static function getRequiredParameters() {
        return array("package");
    }

    public function read() {
        $this->getData();
        return $this->mapping;
    }

    public function setParameter($key, $val) {
        if ($key == "package") {
            $this->package = $val;
        } elseif ($key == "resource") {
            $this->resource = $val;
        }
    }

    public static function getAllowedFormatters() {
        return array();
    }

    private function getData() {

    }

    public static function getDoc() {
        return "Lists a package onthology";
    }

}

?>
