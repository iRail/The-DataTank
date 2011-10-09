<?php
/**
 * This class handles a CSV file
 *
 * @package The-Datatank/model/resources/strategies
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */
include_once ("model/resources/strategies/ATabularData.class.php");
class CSV extends ATabularData {

    public function documentCreateRequiredParameters(){
        return array("uri");
            
    }
    
    //We could specify extra filters here for CSV resources
    public function documentReadRequiredParameters(){
        return array();
    }
    
    public function documentCreateParameters(){
        $parameters = array();
        $parameters["uri"] = "The URI to the CSV file";
        $parameters["columns"] = "The columns that are to be published, if empty every column will be published.";
        $parameters["PK"] = "The primary key of an entry";
        return $parameters;
    }
    
    public function documentReadParameters(){
        return array();
    }

    public function onCall($package, $resource) {
        
        /*
         * First retrieve the values for the generic fields of the CSV logic
         */
        $result = DBQueries::getCSVResource($package, $resource);
        
        $gen_res_id = $result["gen_res_id"];
        
        if (isset($result["uri"])) {
            $filename = $result["uri"];
        } else {
            throw new ResourceTDTException("Can't find URI of the CSV");
        }
        
        $columns = array();
        
        // get the columns from the columns table
        $allowed_columns = DBQueries::getPublishedColumns($gen_res_id);
        $PK = "";
        foreach ($allowed_columns as $result) {
            array_push($columns, $result["column_name"]);
            if ($result["is_primary_key"] == 1) {
                $PK = $result["column_name"];
            }
        }
        
        $resultobject = array();
        $arrayOfRowObjects = array();
        $row = 0;
        
        // only request public available files
        $request = TDT::HttpRequest($filename);
        if (isset($request->error)) {
            throw new CouldNotGetDataTDTException($filename);
        }
        $csv = utf8_encode($request->data);
        
        try {
            // find the delimiter
            $commas = substr_count($csv, ",", 0, strlen($csv)>127?127:strlen($csv));
            $semicolons = substr_count($csv, ";", 0, strlen($csv)>127?127:strlen($csv));
            
            $rows = str_getcsv($csv, "\n");
            
            $fieldhash = array();
            foreach($rows as $row => $fields) {
                $data = str_getcsv($fields, $commas>$semicolons?",":";");
                
                // keys not found yet
                if(!count($fieldhash)) {
                    // <<fast!>> way to detect empty fields
                    // if it contains empty fields, it should not be our field hash
                    $empty_elements = array_keys($data,"");
                    if(!count($empty_elements)) {
                        // we found our key fields
                        for($i = 0; $i < sizeof($data); $i++)
                            $fieldhash[$data[$i]] = $i;
                    }
                } else {
                    $rowobject = new stdClass();
                    $keys = array_keys($fieldhash);
                    for($i = 0; $i < sizeof($keys); $i++) {
                        $c = $keys[$i];
                        if (sizeof($columns) == 0 || in_array($c, $columns)) {
                            $rowobject->$c = $data[$fieldhash[$c]];
                        }
                    }
                    if ($PK == "") {
                        array_push($arrayOfRowObjects, $rowobject);
                    } else {
                        if (! isset($arrayOfRowObjects[$rowobject->$PK])) {
                            $arrayOfRowObjects[$rowobject->$PK] = $rowobject;
                        }
                    }
                }
            }
            
            return $arrayOfRowObjects;
        } catch (Exception $ex) {
            throw new CouldNotGetDataTDTException($filename);
        }
    }
    
    public function onDelete($package, $resource) {
        DBQueries::deleteCSVResource($package, $resource);
    }
    
    public function onAdd($package_id, $resource_id) {
        $this->evaluateCSVResource($resource_id);

        if (!isset($this->PK)){
            
            $this->PK = "";
        }
        if(!isset($this->columns)){
            $this->columns = "";
        }
        
        if ($this->columns != "") {
            parent::evaluateColumns($this->columns, $this->PK, $resource_id);
        }
    } 
   
    private function evaluateCSVResource($resource_id) {
        DBQueries::storeCSVResource($resource_id, $this->uri);
    }
}
?>
