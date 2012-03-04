<?php 
/**
 * This class is returns the number of queries/errors made on/in the API/methods in a certain month.
 *
 * @package The-Datatank/packages/TDTStats
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert   <pieter@iRail.be>
 */

class TDTStatsYear extends AReader{

    public static function getParameters(){
	return array(
            "package" => "Statistics about this package (\"all\" selects all packages)",
            "resource" => "Statistics about this resource (\"all\" selects all packages)"

        );
    }

    public static function getRequiredParameters(){
        return array("package", "resource");
    }

    public function setParameter($key,$val){
        switch($key){
            case "package":
                $this->package = $val;
                break;
            case "resource":
                $this->resource = $val;
                break;
			// commented out because formatters can also have parameters
            //default:
            //    throw new ParameterTDTException($key);
        }
    }

    public function read(){
        //prepare arguments
        $arguments[":package"] = $this->package;
        $arguments[":resource"] = $this->resource;
        //prepare the where clause
        if($this->package == "all" && $this->resource = "all"){
            $clause = "1";
        }else if($this->package == "all"){
            $clause = "resource=:resource";
            unset($arguments[":package"]);
        }else if($this->resource == "all"){
            $clause = "package=:package";
            unset($arguments[":resource"]);
        }else {
            $clause = "package=:package and resource=:resource";
        }
        
        //group everything by month and count all requests during this time.
        //To be considered: should we cache this?
        $qresult = R::getAll(
            "SELECT count(1) as requests, time, from_unixtime(time, '%Y') as year, from_unixtime(time, '%m') as month
             FROM  requests 
             WHERE $clause
             GROUP BY from_unixtime(time,'%M %Y')",
            $arguments
        );
        //Now let's reorder everything: by year -> month 
        $result = array();
        //TODO: fill the gaps!
        foreach($qresult as $row){
            if(!isset($result[$row["year"]])){
                $result[$row["year"]] = array();
            }
            $result[$row["year"]][] = array(
                "month" => $row["month"],
                "requests" => $row["requests"],
                //"useragent" => "nyimplemented",
                //"errors" => "nyimplemented",
                //"languages" => "nyimplemented"
            );
            
        }
        return $result;
    }

    public static function getDoc(){
        return "Lists statistics about a certain month in the history of this The DataTank instance";
    }

}
?>
