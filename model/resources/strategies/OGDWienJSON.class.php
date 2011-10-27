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

    public function documentCreateParameters(){
        return array("url" => "The url of where the OGD Wien JSON is found.",
                     "columns" => "The columns that are to be published from the OGD Wien JSON.",
                     "PK" => "The primary key of each row."
        );  
    }
    
    public function documentCreateRequiredParameters(){
        return array("url",  "columns", "PK");    
    }

    public function documentReadRequiredParameters(){
        return array();
    }
    
    public function documentReadParameters(){
        return array("long", "lat", "radius");
    }
    

    public function __construct(){
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
       
        //$columns = array();
        
        // get the columns from the columns table
        $allowed_columns = DBQueries::getPublishedColumns($gen_res_id);
            
        $columns = array();
        $PK = "id";
        foreach($allowed_columns as $result){
            array_push($columns,$result["column_name"]);
        }
        $arrayOfRowObjects = array();
        $row = 0;
     
        try { 

            $json = file_get_contents($url,0,null,null);
            $json = utf8_encode($json);
            $json = json_decode($json);
            
            foreach($json->features as $feature) {
                $distance = NULL;
                if (isset($this->radius) && isset($this->long) && isset($this->lat)) {
                    $olat = $feature->geometry->coordinates[1];
                    $olon = $feature->geometry->coordinates[0];
                    $R = 6371; // earth�s radius in km
                    $dLat = deg2rad($this->lat-$olat);
                    $dLon = deg2rad($this->long-$olon);
                    $rolat = deg2rad($olat);
                    $rlat = deg2rad($this->lat);

                    $a = sin($dLat/2) * sin($dLat/2) + sin($dLon/2) * sin($dLon/2) * cos($rolat) * cos($rlat); 
                    $c = 2 * atan2(sqrt($a), sqrt(1-$a)); 
                    $distance = $R * $c;             
                }
                if(!isset($distance) || $distance < $this->radius) {
                    $rowobject = new stdClass();    
                    $rowobject->id = substr($feature->id, strpos($feature->id,".") + 1);
                    $rowobject->long = $feature->geometry->coordinates[0];
                    $rowobject->lat = $feature->geometry->coordinates[1];
                    // distance is null when no geo search is performed
                    $rowobject->distance = round($distance,3); // round on 1m precision
                    
                    foreach($feature->properties as $property => $value) {
                        $property = strtolower($property);
                        if(sizeof($columns) == 0 || in_array($property,$columns)) {                        
                            $rowobject->$property = $value;
                        }
                    }
                    $arrayOfRowObjects[$rowobject->id] = $rowobject;
                }
            }

            return $arrayOfRowObjects;
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
    
    public function getFields($package, $resource) {
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
       
        //$columns = array();
        
        // get the columns from the columns table
        $allowed_columns = DBQueries::getPublishedColumns($gen_res_id);
            
        $columns = array();
        $PK = "id";
        foreach($allowed_columns as $result){
            array_push($columns,$result["column_name"]);
        }
        $arrayOfRowObjects = array();
        $row = 0;
     
        try { 

            $json = file_get_contents($url,0,null,null);
            $json = utf8_encode($json);
            $json = json_decode($json);
            
            foreach($json->features as $feature) {
                $arr = array();
                foreach ($feature->properties as $key => $value)
                    $arr[] = strtolower ($key);
                
                $arr = array_merge($arr,array('id', 'long','lat','distance'));
                
                
                                
                return $arr;
                break;
            }
        }catch( Exception $ex) {
            throw new CouldNotGetDataTDTException( $url );
        }
    
    }

}
?>
