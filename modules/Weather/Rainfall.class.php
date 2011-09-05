<?php
/**
 * 
 * @package The-Datatank/modules/Weather/
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert   <pieter@iRail.be>
 */

class Rainfall extends AResource{

    public static function getParameters(){
	return array(
            "lat" => "The latitude of the requested location",
            "long" => "The longitude of the requested location"
        );
    }

    public static function getRequiredParameters(){
	return array("lat","long");
    }

    public function setParameter($key,$val){
        $this->$key = $val;
    }

    public function call(){
        $o = new stdClass();
        $options = array("cache-time" => 60*5); //cache this for 5 minutes
        $request = TDT::HttpRequest("http://gps.buienradar.nl/getrr.php?lat=".$this->lat."&lon=" . $this->long, $options);
        $rows = explode("\n",$request->data);
        $o->position = new Location($this->long, $this->lat, "User's location", "This is the requested location of the user");        
        foreach($rows as $row){
            if($row != ""){
                $columns = explode("|",$row);
                preg_match("/(\d\d):(\d\d)/si",$columns[1], $matches);
                $neerslag = new Time(mktime($matches[1], $matches[2]));
                $neerslag->milimeter = $columns[0];
                $o->neerslag[] = $neerslag;
            }
        } 
	return $o;
    }
     
    public static function getAllowedPrintMethods(){
	return array("json","xml", "jsonp", "php", "html", "kml");
    }

    public static function getDoc(){
	return "Will return the amount of predicted rainfall for a certain location for the next couple of hours";
    }
}

?>
