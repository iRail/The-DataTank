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
        
        $resultobject = new stdClass();
        $arrayOfRowObjects = array();
        $row = 0;
     
        try { 

            $json = file_get_contents($url,0,null,null);
            $json = utf8_encode($json);
            $json = json_decode($json);

            foreach($json->features as $feature) {
                $rowobject = new stdClass();    
                $rowobject->id = $feature->id;
                $rowobject->long = $feature->geometry->coordinates[0];
                $rowobject->lat = $feature->geometry->coordinates[1];
                foreach($feature->properties as $property => $value) {
                    $property = strtolower($property);
                    if(sizeof($columns) == 0 || in_array($property,$columns)) {                        
                        $rowobject->$property = $value;
                    }
                }
                $arrayOfRowObjects[$rowobject->id] = $rowobject;
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
        //If PK not available, code gives error. PK should not be a required parameter!
        //parent::evaluateColumns($this->columns,$this->PK,$resource_id);
        parent::evaluateColumns($this->columns,"id",$resource_id);
    }

    private function evaluateOGDWienJSONResource($resource_id){
        DBQueries::storeOGDWienJSONResource($resource_id, $this->url);
    }    
}
?>
