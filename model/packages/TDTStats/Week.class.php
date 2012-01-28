<?php 
/**
 * Lists statistics about a certain week in the history of this The DataTank instance
 *
 * @package The-Datatank/packages/TDTStats
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert   <pieter@iRail.be>
 */

class TDTStatsWeek extends AReader{    

    public static function getParameters(){
	return array(
            "package" => "Statistics about this package (\"all\" selects all packages)",
            "resource" => "Statistics about this resource (\"all\" selects all packages)",
            "year" => "Year in XXXX format",
            "week" => "Number of the week in the year with trailing 0"
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
            case "week":
                $this->week = $val;
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
        //TODO
        return new stdClass();
    }

    public static function getDoc(){
        return "Lists statistics about a certain week in the history of this The DataTank instance";
    }

}
?>
