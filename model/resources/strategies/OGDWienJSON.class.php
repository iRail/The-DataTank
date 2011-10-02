<?php
/**
 * This class handles a XLS file
 *
 * @package The-Datatank/model/resources/strategies
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Lieven Janssen
 */
include_once("model/resources/strategies/ATabularData.class.php");

class OGDWienJSON extends ATabularData {


    public function __construct(){
        $this->parameters["url"] = "The url of where the OGD Wien JSON is found.";
        $this->parameters["columns"] = "The columns that are to be published from the OGD Wien JSON.";
        //$this->parameters["PK"] = "The primary key of each row.";

        $this->requiredParameters = array_merge($this->requiredParameters, array_keys($this->parameters));

        // doesn't seem to work
        $this->parameters["long"] = "The longitude of the point where you want to search from.";
        $this->parameters["lat"] = "The latitude of the point where you want to search from.";
        $this->parameters["radius"] = "The radius in km around the point.";
    }

    public function onCall($package,$resource){

        /*
         * First retrieve the values for the generic fields of the OGD Wien JSON logic
         */
        $result = DBQueries::getOGDWienJSONResource($package, $resource);
        
        $gen_res_id = $result["gen_res_id"];

        if(isset($result["url"])){
            $url = $result["url"];
        }else{
            throw new ResourceTDTException("Can't find url of the OGD Wien JSON.");
        }
//echo $this->radius;
        //$columns = array();
        
        // get the columns from the columns table
        $allowed_columns = DBQueries::getPublishedColumns($gen_res_id);
            
        $columns = array();
        $PK = "id";
        foreach($allowed_columns as $result){
            array_push($columns,$result["column_name"]);
        }
        
        $resultobject = new stdClass();
        $arrayOfRowObjects = array();
        $row = 0;
     
        try { 

            $json = file_get_contents($url,0,null,null);
            $json = utf8_encode($json);
            $json = json_decode($json);
            
            $radius = NULL; //radius parameter (in km), must be NULL when not provided
            $lon = NULL; //long parameter, must be NULL when not provided
            $lat = NULL; //lat parameter, must be NULL when not provided

            // test
            /*
            $radius = 5;
            $lon = 16.369698225576;
            $lat = 48.16885513122;
            */

            foreach($json->features as $feature) {
                $distance = NULL;
                if (isset($radius) && isset($lon) && isset($lat)) {
                    $olat = $feature->geometry->coordinates[1];
                    $olon = $feature->geometry->coordinates[0];
                    $R = 6371; // earth�s radius in km
                    $dLat = deg2rad($lat-$olat);
                    $dLon = deg2rad($lon-$olon);
                    $rolat = deg2rad($olat);
                    $rlat = deg2rad($lat);

                    $a = sin($dLat/2) * sin($dLat/2) + sin($dLon/2) * sin($dLon/2) * cos($rolat) * cos($rlat); 
                    $c = 2 * atan2(sqrt($a), sqrt(1-$a)); 
                    $distance = $R * $c;             
                }
                if(!isset($distance) || $distance < $radius) {
                    $rowobject = new stdClass();    
                    $rowobject->id = substr($feature->id, strpos($feature->id,".") + 1);
                    $rowobject->long = $feature->geometry->coordinates[0];
                    $rowobject->lat = $feature->geometry->coordinates[1];
                    // distance is null when no geo search is performed
                    $rowobject->distance = $distance;
                    
                    foreach($feature->properties as $property => $value) {
                        $property = strtolower($property);
                        if(sizeof($columns) == 0 || in_array($property,$columns)) {                        
                            $rowobject->$property = $value;
                        }
                    }
                    $arrayOfRowObjects[$rowobject->id] = $rowobject;
                }
            }

            $resultobject->object = $arrayOfRowObjects;
            return $resultobject;
        } catch( Exception $ex) {
            throw new CouldNotGetDataTDTException( $url );
        }
    }

    public function onDelete($package,$resource){
        DBQueries::deleteOGDWienJSONResource($package, $resource);
    }

    public function onAdd($package_id,$resource_id){
        $this->evaluateOGDWienJSONResource($resource_id);

        if(!isset($this->columns)){
            $this->columns = "";
        }
        
        if ($this->columns != "") {
            parent::evaluateColumns($this->columns, "id", $resource_id);
        }
    }

    private function evaluateOGDWienJSONResource($resource_id){
        DBQueries::storeOGDWienJSONResource($resource_id, $this->url);
    }    
}
?>
