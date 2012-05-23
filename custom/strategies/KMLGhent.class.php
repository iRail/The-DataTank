<?php
/**
 * This class handles a KML file
 *
 * @package The-Datatank/custom/strategies
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Lieven Janssen
 */
include_once("custom/strategies/ATabularData.class.php");

class KMLGhent extends ATabularData {

    /**
     * Returns an array with params => documentation pairs that can be used to create this type of resource.
     * @return array with parameter => documentation pairs
     */
    public function documentCreateParameters(){
        return array("uri" => "The uri of where the KML of City of Ghent is found.",
                     "columns" => "The columns that are to be published from the KML.",
                     "PK" => "The primary key of each row."
        );  
    }
    
    /**
     * Returns an array with params => documentation pairs who are required to create this type of resource.
     * @return array with parameter => documentation pairs
     */
    public function documentCreateRequiredParameters(){
        return array("uri");    
    }

    /**
     * Document all the read required parameters for documentation purposes. 
     * @return array with necessary parameters to read this type of resource.
     */
    public function documentReadRequiredParameters(){
        return array();
    }
    
    /**
     * Returns an array with parameter => documentation pairs that can be used to read this resource.
     * @return array with parameter => documentation pairs
     */
    public function documentReadParameters(){
        return array("long" => "The longitude of the centerpoint.",
                     "lat" => "The latitude of the centerpoint.",
                     "radius" => "The radius that will reside from the centerpoint and form a circle that will act as a geo-spatial filter.");
    }
    

    protected function isValid($package_id,$generic_resource_id) {
        if(!isset($this->uri)){
            $this->throwException($package_id,$generic_resource_id, "Can't find uri of the KML");
        }
		
        if (!isset($this->columns)) {
            $this->columns = array();
        }

        if (!isset($this->PK)) {
            $this->PK = "id";
        }

        $uri = $this->uri;
        $columns = $this->columns;
        
        if(empty($this->columns)) {
            try { 
                //Allows our handler to deal with the error. This error will then be logged.
                //If a severe error is passed this will be shown as an internal error to the user
                //This isn't always the most elegant message, but it allows to inform the developers with 
                //very specific information if it turns out that it's not the users fault that caused the error.
                libxml_use_internal_errors(true);
                $xml = @simplexml_load_file($uri);
                            
                $xmlns = $xml->getDocNamespaces();
                $xmlns = $xmlns[''];
                $xml->registerXPathNamespace('kml', $xmlns);
                $placemarks = $xml->xpath('//kml:Placemark');

                foreach ($placemarks as $placemark) {
                    $placemark->registerXPathNamespace('kml', $xmlns);
                    $properties = $placemark->xpath('kml:ExtendedData/kml:SchemaData/kml:SimpleData');
                    foreach ($properties as $property) {
                        $name = $property->xpath('@name');
                        $name = strtolower($name[0]);
                        $this->columns[$name] = $name;
                    }
                    $coordinates = $placemark->xpath('kml:Point/kml:coordinates');
                    if($coordinates != false) {
                        $this->columns["long"] = "long";
                        $this->columns["lat"] = "lat";
                    } else {
                        $coordinates = $placemark->xpath('kml:Polygon/kml:outerBoundaryIs/kml:LinearRing/kml:coordinates');	
                        if($coordinates != false) {
                            $this->columns["coords"] = "coords";
                        } else {
                            $coordinates = $placemark->xpath('kml:MultiGeometry/kml:Polygon/kml:outerBoundaryIs/kml:LinearRing/kml:coordinates');	
                            if($coordinates != false) {
                                $this->columns["coords"] = "coords";
                            }                        
                        }                        
                    }
                    $this->columns["id"] = "id";
                    $this->columns["distance"] = "distance";
                }
            } catch( Exception $ex) {
                throw new CouldNotGetDataTDTException( $uri );
            }
        }
		
        return true;
    }

    public function read(&$configObject,$package,$resource){
        set_time_limit(1000);
	
        parent::read($configObject,$package,$resource);
       
        if(isset($configObject->uri)){
            $uri = $configObject->uri;
        }else{
            throw new ResourceTDTException("Can't find uri of the KML");
        }
		
        $columns = array();
        
        $PK = $configObject->PK;
            
        $columns = $configObject->columns;

        //$gen_res_id = $configObject->gen_res_id;
        
        $resultobject = new stdClass();
        $arrayOfRowObjects = array();
     
        try { 

            $xml = simplexml_load_file($uri);

            $xmlns = $xml->getDocNamespaces();
            $xmlns = $xmlns[''];

            $xml->registerXPathNamespace('kml', $xmlns);
            $placemarks = $xml->xpath('//kml:Placemark');

            foreach ($placemarks as $placemark) {
                $rowobject = new stdClass();
                $include = false;
                $placemark->registerXPathNamespace('kml', $xmlns);

                $coordinates = $placemark->xpath('kml:Point/kml:coordinates');
                if($coordinates != false) {
                    $coordarr = explode(',',$coordinates[0]);
                    $lat = $coordarr[1];
                    $long = $coordarr[0]; 
                    $distance = NULL;
                    if (isset($this->radius) && isset($this->long) && isset($this->lat)) {
                        $olat = $lat;
                        $olon = $long;
                        $R = 6371; // earth's radius in km
                        $dLat = deg2rad($this->lat-$olat);
                        $dLon = deg2rad($this->long-$olon);
                        $rolat = deg2rad($olat);
                        $rlat = deg2rad($this->lat);

                        $a = sin($dLat/2) * sin($dLat/2) + sin($dLon/2) * sin($dLon/2) * cos($rolat) * cos($rlat); 
                        $c = 2 * atan2(sqrt($a), sqrt(1-$a)); 
                        $distance = $R * $c;             
                    }
                    if(!isset($distance) || $distance < $this->radius) {
                        $include = true;
                        $rowobject->long = $long;
                        $rowobject->lat = $lat;
                        $rowobject->distance = round($distance,3); // round on 1m precision
                    }
                } else {
                    $include = true;
                    $coordinates = $placemark->xpath('kml:Polygon/kml:outerBoundaryIs/kml:LinearRing/kml:coordinates');	
                    if($coordinates != false) {
                        $rowobject->coords = (String) $coordinates[0];
                    } else {
                        $coordinates = $placemark->xpath('kml:MultiGeometry/kml:Polygon/kml:outerBoundaryIs/kml:LinearRing/kml:coordinates');	
                        if($coordinates != false) {
                            $firstrow = true;
                            foreach($coordinates as $value) {
                                if ($firstrow) {
                                    $rowobject->coords = $value;
                                } else {
                                    $rowobject->coords .= "|" . $value;
                                }
                                $firstrow = false;
                            }
                        }                        
                    }                        
                }
				
                if($include) {
                    $id = $placemark->xpath('@id');
                    $id = (string)$id[0];

                    $rowobject->id = $id;
                    $properties = $placemark->xpath('kml:ExtendedData/kml:SchemaData/kml:SimpleData');
                    foreach ($properties as $property) {
                        $name = $property->xpath('@name');
                        $name = strtolower($name[0]);
                        if(sizeof($columns) == 0 || in_array($name,$columns)) {                        
                            $rowobject->$name = (string)$property[0];
                        }
                    }
                    if ($PK == "") {
                        array_push($arrayOfRowObjects, $rowobject);
                    } else {
                        if (!isset($arrayOfRowObjects[$rowobject->$PK])) {
                            $arrayOfRowObjects[$rowobject->$PK] = $rowobject;
                        }
                    }

                    //$arrayOfRowObjects[$rowobject->id] = $rowobject;
                    //array_push($arrayOfRowObjects, $rowobject);
                }
            }

            return $arrayOfRowObjects;
        } catch( Exception $ex) {
            throw new CouldNotGetDataTDTException( $uri );
        }
    }
}
?>
