<?php
/**
 * This class handles a XLS file
 *
 * @package The-Datatank/custom/strategies
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Lieven Janssen
 */
include_once("custom/strategies/ATabularData.class.php");

class OGDWienJSON extends ATabularData {

    /**
     * Returns an array with params => documentation pairs that can be used to create this type of resource.
     * @return array with parameter => documentation pairs
     */
    public function documentCreateParameters(){
        return array("uri" => "The uri of where the OGD Wien JSON is found.",
                     "columns" => "The columns that are to be published from the OGD Wien JSON.",
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
        return array("long", "lat", "radius");
    }
    

    protected function isValid($package_id,$generic_resource_id) {
        if(!isset($this->uri)){
            $this->throwException($package_id,$generic_resource_id, "Can't find uri of the OGD Wien JSON");
        }
		
        if (!isset($this->columns)) {
            $this->columns = array();
        }

        if (!isset($this->PK)) {
            $this->PK = "id";
        }

        $uri = $this->uri;
        $columns = $this->columns;
        
        if(empty($this->columns)){ 
            try { 

                $json = file_get_contents($uri,0,null,null);
                $json = utf8_encode($json);
                $json = json_decode($json);
                
                // exception will be the same as the catched one, yet the check for a null value
                // will result in a controlled error, if the json is null, the error of php will be thrown
                // because it's a fatal one.
                if(is_null($json)){
                    throw new CouldNotGetDataTDTException($uri);
                }
                
                if(!isset($json->features)){
                    throw new ResourceAdditionTDTException("We could not find the features property, which may indicate this is not a OGDWienJSON json file.");
                }
                

                $feature = $json->features[0];
				
                foreach($feature->properties as $property => $value) {
                    $property = strtolower($property);
                    $this->columns[$property] = $property;
                }
                $this->columns["id"] = "id";
                $this->columns["long"] = "long";
                $this->columns["lat"] = "lat";
                $this->columns["distance"] = "distance";
            } catch( Exception $ex) {
                throw new CouldNotGetDataTDTException( $uri );
            }
        }
		
        return true;
    }

    public function read(&$configObject){
        set_time_limit(1000);
	
        parent::read($configObject);
       
        if(isset($configObject->uri)){
            $uri = $configObject->uri;
        }else{
            throw new ResourceTDTException("Can't find uri of the OGD Wien Json");
        }
		
        $columns = array();
        
        $PK = $configObject->PK;
            
        $columns = $configObject->columns;
        
        $resultobject = new stdClass();
        $arrayOfRowObjects = array();
        $row = 0;
     
        try { 

            $json = file_get_contents($uri,0,null,null);
            $json = utf8_encode($json);
            $json = json_decode($json);
            
            foreach($json->features as $feature) {
                $distance = NULL;
                if (isset($this->radius) && isset($this->long) && isset($this->lat)) {
                    $olat = $feature->geometry->coordinates[1];
                    $olon = $feature->geometry->coordinates[0];
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
            throw new CouldNotGetDataTDTException( $uri );
        }
    }
}
?>
