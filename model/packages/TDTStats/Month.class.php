<?php 
/**
 * This class is returns the number of queries/errors made on/in the API/methods in a certain month.
 *
 * @package The-Datatank/packages/TDTStats
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert   <pieter@iRail.be>
 */

class TDTStatsMonth extends AReader{    

    public static function getParameters(){
	return array(
            "package" => "Statistics about this package (\"all\" selects all packages)",
            "resource" => "Statistics about this resource (\"all\" selects all packages)",
            "year" => "Year in XXXX format",
            "month" => "Month with trailing 0: 01 is January"
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
            case "year":
                $this->year = $val;
                break;
            case "month":
                $this->month = $val;
                break;
            default:
                throw new ParameterTDTException($key);
        }
    }

    private function osort(&$array, $prop){
        usort($array, function($a, $b) use ($prop) {
                return $a->$prop > $b->$prop ? 1 : -1;
            }); 
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
        }else if($this->resource == "all"){
            $clause = "package=:package";
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
        foreach($qresult as $row){
            if(!isset($result[$row["year"]])){
                $result[$row["year"]] = array();
            }
            if(!isset($result[$row["year"]][$row["month"]])){
                $result[$row["year"]][$row["month"]] = array();
            }
            $result[$row["year"]][$row["month"]]["requests"] = $row["requests"];
//            $result[$row["year"]][$row["month"]]["topuseragent"] = "nyimplemented";
//            $result[$row["year"]][$row["month"]]["errors"] = "nyimplemented";
//            $result[$row["year"]][$row["month"]]["toplanguages"] = "nyimplemented";
        }
        return $result;
    }

    public static function getDoc(){
        return "Lists statistics about a certain month in the history of this The DataTank instance";
    }

}
?>
