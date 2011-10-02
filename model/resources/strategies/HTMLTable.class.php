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

class HTMLTable extends ATabularData {


    public function __construct(){
        $this->parameters["url"] = "The url of where the HTML table is found.";
        $this->parameters["xpath"]  = "The XPath to the HTML table";
        $this->parameters["columns"] = "The columns that are to be published from the HTML table.";
        $this->parameters["PK"] = "The primary key of each row.";

        $this->requiredParameters = array_merge($this->requiredParameters, array_keys($this->parameters));
    }

    public function onCall($package,$resource){

        /*
         * First retrieve the values for the generic fields of the HTML Table logic
         */
        $result = DBQueries::getHTMLTableResource($package, $resource);
        
        $gen_res_id = $result["gen_res_id"];

        if(isset($result["url"])){
            $url = $result["url"];
        }else{
            throw new ResourceTDTException("Can't find url of the HTML Table");
        }
		
        if(isset($result["xpath"])){
            $xpath = $result["xpath"];
        }else{
            throw new ResourceTDTException("Can't find xpath of the HTML Table");
        }		

        $columns = array();
        
        // get the columns from the columns table
        $allowed_columns = DBQueries::getPublishedColumns($gen_res_id);
            
        $columns = array();
        $PK = "";
        foreach($allowed_columns as $result){
            array_push($columns,$result["column_name"]);
            if($result["is_primary_key"] == 1){
                $PK = $result["column_name"];
            }
        }
        
        $resultobject = new stdClass();
        $arrayOfRowObjects = array();
        $row = 0;
     
        try { 

            $oldSetting = libxml_use_internal_errors( true ); 
            libxml_clear_errors(); 
             
            $html = new DOMDocument(); 
            $html->loadHtmlFile($url); 
             
            $domxpath = new DOMXPath( $html ); 
            $tablerows = $domxpath->query($xpath . "/tr" ); 
            if ($tablerows->length == 0) {
                //table has thead and tbody
                $tablerows = $domxpath->query($xpath . "/*/tr" );
            }

            $rowIndex = 1;
            foreach ($tablerows as $tr) {
                $newDom = new DOMDocument;
                $newDom->appendChild($newDom->importNode($tr,true));
                
                $domxpath = new DOMXPath( $newDom ); 
                if ($rowIndex == 1) {
                    $tablecols = $domxpath->query("td");
                    if ($tablecols->length == 0) {
                        //thead row has th instead of td
                        $tablecols = $domxpath->query("th" );
                    }
                    $columnIndex = 1;
                    foreach($tablecols as $td) {
                        $fieldhash[ $td->nodeValue ] = $columnIndex;						
                        $columnIndex++;
                    }
                } else {
                    $tablecols = $domxpath->query("td");
                    $columnIndex = 1;
                    $rowobject = new stdClass();
                    $keys = array_keys($fieldhash);
                    foreach($tablecols as $td) {
                        $c = $keys[$columnIndex - 1];
                        if(sizeof($columns) == 0 || in_array($c,$columns)){
                            $rowobject->$c = $td->nodeValue;
                        }
                        $columnIndex++;
                    }    
                    if($PK == "") {
                        array_push($arrayOfRowObjects,$rowobject);   
                    } else {
                        if(!isset($arrayOfRowObjects[$rowobject->$PK])){
                            $arrayOfRowObjects[$rowobject->$PK] = $rowobject;
                        }
                    }
                }
                $rowIndex++;
            }

            $resultobject->object = $arrayOfRowObjects;
            return $resultobject;
        } catch( Exception $ex) {
            throw new CouldNotGetDataTDTException( $url );
        }
    }

    public function onDelete($package,$resource){
        DBQueries::deleteHTMLTableResource($package, $resource);
    }

    public function onAdd($package_id,$resource_id){
        $this->evaluateHTMLTableResource($resource_id);
        
        if (!isset($this->PK))
            $this->PK = "";
        }
        if(!isset($this->columns)){
            $this->columns = "";
        }
        
        if ($this->columns != "") {
            parent::evaluateColumns($this->columns, $this->PK, $resource_id);
        }
    }

    private function evaluateHTMLTableResource($resource_id){
        DBQueries::storeHTMLTableResource($resource_id, $this->url, $this->xpath);
    }    
}
?>
