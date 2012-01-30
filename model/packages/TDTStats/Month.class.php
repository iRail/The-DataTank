<?php 
/**
 * This class is returns the number of queries/errors made on/in the API/methods on a certain month.
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
        return array("package", "resource","year","month");
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

    public function read(){
        
        //prepare arguments
        $arguments[":package"] = $this->package;
        $arguments[":resource"] = $this->resource;
        $arguments[":month"] = $this->month;
        $arguments[":year"] = $this->year;

        //prepare the where clause
        if($this->package == "all" && $this->resource = "all"){
            $clause = "1";
            unset($arguments[":package"]);
            unset($arguments[":resource"]);
        }else if($this->package == "all"){
            $clause = "resource=:resource";
            unset($arguments[":package"]);
        }else if($this->resource == "all"){
            $clause = "package=:package";
            unset($arguments[":resource"]);
        }else {
            $clause = "package=:package and resource=:resource";
        }
        
        $clause .= " and from_unixtime(time,'%m')=:month and from_unixtime(time,'%Y')=:year";

        
        //group everything by month and count all requests during this time.
        //To be considered: should we cache this?
        $qresult = R::getAll(
            "SELECT count(1) as requests, time, from_unixtime(time,'%d') as day
             FROM  requests 
             WHERE $clause
             GROUP BY from_unixtime(time,'%d %M %Y')",
            $arguments
        );
        //Now let's reorder everything: by year -> month 
        $result = array();
        foreach($qresult as $row){
            $result[$row["day"]] = array();
            $result[$row["day"]]["requests"] = $row["requests"];
//            $result[$row["day"]]["useragents"] = "nyimplemented";
//            $result[$row["day"]]["errors"] = "nyimplemented";
//            $result[$row["day"]]["languages"] = "nyimplemented";
        }
        return $result;
    }

    public static function getDoc(){
        return "Lists statistics about a certain month in the history of this The DataTank instance";
    }

}
?>
