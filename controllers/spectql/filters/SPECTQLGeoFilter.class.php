<?php
/**
 * Implements a filter: ?
 *
 * @package The-Datatank/controllers/spectql/filters
 * @copyright (C) 2011 by OKFN chapter Belgium vzw/asbl
 * @license LGPL
 * @author Pieter Colpaert
 * @organisation FlatTurtle
 */
class SPECTQLGeoFilter{
    private $long, $lat, $radius;

    public function __construct($lat, $long, $radius){
        $this->long = $long;
        $this->lat = $lat;
        $this->radius = $radius;
    }
    
    public function execute(&$current){
        if(!is_array($current)){
            throw new ParserTDTException("The resource you have specified is not a resource we can filter");
        }

        $result = array();
        foreach($current as &$row){
            if(isset($row["latitude"]) && isset($row["longitude"]) && $this->in_radius($row["latitude"],$row["longitude"])){
                array_push($result,$row);
            }elseif(isset($row["lat"]) && isset($row["long"]) && $this->in_radius($row["lat"],$row["long"])){
                array_push($result,$row);
            }
        }
        $current = $result;
    }

    public function in_radius($lat,$long){
        $R = 6371; // earth's radius in km
        $dLat = deg2rad($this->lat - $lat);
        $dLon = deg2rad($this->long - $long);
        $rolat = deg2rad($lat);
        $rlat = deg2rad($this->lat);
        $a = sin($dLat/2) * sin($dLat/2) + sin($dLon/2) * sin($dLon/2) * cos($rolat) * cos($rlat); 
        $c = 2 * atan2(sqrt($a), sqrt(1-$a)); 
        $distance = $R * $c;
        return $distance < $this->radius;
    }
}