<?php 
/**
 * This class is returns the number of queries/errors made on/in the API/methods per day.
 *
 * @package The-Datatank/modules/TDTInfo
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert   <pieter@iRail.be>
 * @author Jan Vansteenlandt <jan@iRail.be>
 */

class Queries extends AResource{
    // must be set! Contains the value of the module that needs to be analysed.
    private $module; 
    // if set only look at certain data from a certain method within the given module.
    private $resource;
    // if set, get data from errors table. ( not set or true )
    private $errors = "";
    private $queryResults;

    public static function getParameters(){
	return array("module" => "Name of a module that needs to be analysed, must be set !",
		     "resource" => "Name of a resource within the given module, is not required.",
		     "error" => "If set then the analysis will get it's data from the error table if not from the request table."
	);
    }

    public static function getRequiredParameters(){
        return array("module");
    }

    public function setParameter($key,$val){
        if($key == "module"){
            $this->module = $val;
        }elseif($key == "resource"){
            $this->resource = $val;
        }elseif($key == "error"){
            $this->errors = $val;
        }
    }

    private function getData() {
        R::setup(Config::$DB, Config::$MySQL_USER_NAME, Config::$MySQL_PASSWORD);

        /* Send a query to the server */
        if($this->errors == ""){
            $databasetable = "requests";
        }else{
            $databasetable = "errors";
        }

        $queries = R::find(
            $databasetable,
            "url_request regexp ':module/:resource' GROUP BY " .
            "from_unixtime(time , ,'%D %M %Y')",
            array(':module' => $this->module, ':resource' => $this->resource)
        );

        $this->queryResults = new stdClass();
        $this->queryResults->result = $queries;
    }

    public function call(){
        $this->getData();
        return $this->queryResults;
    }

    public static function getAllowedPrintMethods(){
        return array("json","xml", "jsonp", "php", "html");
    }

    public static function getDoc(){
        return "Lists the number of queries(requests/errors) to this datatank"
            . "instance per day";
    }
}
?>
