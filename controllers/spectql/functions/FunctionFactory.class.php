<?php
/**
 * The function factory creates function according to a given name.
 *
 * @package The-Datatank/controllers/spectql/functions
 * @copyright (C) 2011 by OKFN chapter Belgium vzw/asbl
 * @license LGPL
 * @author Pieter Colpaert
 * @organisation Hogent
 */

class FunctionFactory {
    
    private static $instance;
    
    private $functions = array (
        "count" => "SPECTQLCount",
        "max" => "SPECTQLMax",
        "min" => "SPECTQLMin",
        "avg" => "SPECTQLAvg",
        "limit" => "SPECTQLLimit",
        "distance" => "SPECTQLDistance"
    );

    public static function getInstance(){
        if(!isset($instance)){
            self::$instance = new FunctionFactory();
        }
        return self::$instance;
    }

    private function __construct(){}

    public function createFunction($name, $argument, $argument2 = ""){
        if(isset($this->functions[$name]) && file_exists("controllers/spectql/functions/" . $this->functions[$name] . ".class.php")){
            include_once("controllers/spectql/functions/" . $this->functions[$name] . ".class.php");
            if($argument2 == "")
                return new $this->functions[$name]($name,$argument);
            return new $this->functions[$name]($name,$argument,$argument2);
        }
        throw new ParserTDTException("Unknown function: " . $name);
    }    
}
