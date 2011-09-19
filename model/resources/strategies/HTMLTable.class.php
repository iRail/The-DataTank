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

    public function __construct() {
        if(Config::$PHPEXCEL_IOFACTORY_PATH!="") {
            if(!file_exists(Config::$PHPEXCEL_IOFACTORY_PATH)){
                throw new NotFoundTDTException("Could not include " . Config::$PHPEXCEL_IOFACTORY_PATH);
            } else {
                include_once(Config::$PHPEXCEL_IOFACTORY_PATH);
            }
        } else {
            throw new NotFoundTDTException("PHPExcel path not defined in config.class");		
        }
    }

    public function onCall($package,$resource){

        /*
         * First retrieve the values for the generic fields of the HTML Table logic
         */
        $result = DBQueries::getHTMLTableResource($package, $resource);
        
        $gen_res_id = $result["gen_res_id"];

        if(isset($result["uri"])){
            $uri = $result["uri"];
        }else{
            throw new ResourceTDTException("Can't find URI of the HTML Table");
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
          
/*
        if(!file_exists($uri)){
            throw new CouldNotGetDataTDTException($uri);
        }
*/        
        try { 

            $oldSetting = libxml_use_internal_errors( true ); 
            libxml_clear_errors(); 
             
            $html = new DOMDocument(); 
            $html->loadHtmlFile($uri); 
             
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
            throw new CouldNotGetDataTDTException( $uri );
        }
    }

    public function onDelete($package,$resource){
        DBQueries::deleteHTMLTableResource($package, $resource);
    }

    public function onAdd($package_id,$resource_id,$content){
        $this->evaluateHTMLTableResource($resource_id,$content);
        parent::evaluateColumns($content["columns"],$content["PK"],$resource_id);
    }

    public function onUpdate($package,$resource,$content){
        // At the moment there's no request for foreign relationships between XLS files
        // Yet this could be perfectly possible!
    }
    

    private function evaluateHTMLTableResource($resource_id,$content){
        DBQueries::storeHTMLTableResource($resource_id, $content["uri"], $content["xpath"]);
    }    
}
?>
