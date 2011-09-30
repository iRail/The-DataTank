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

class XLS extends ATabularData {

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
        $this->parameters["url"] = "The path to the excel sheet (can be a url as well).";
        $this->parameters["sheet"] = "The sheet name of the excel";
        $this->parameters["columns"] = "The columns that are to be published.";
        $this->parameters["PK"] = "The primary key for each row.";
        $this->requiredParameters = array_merge($this->requiredParameters, array_keys($this->parameters));
    }

    public function onCall($package,$resource){

        /*
         * First retrieve the values for the generic fields of the XLS logic
         */
        $result = DBQueries::getXLSResource($package, $resource);
        
        $gen_res_id = $result["gen_res_id"];

        if(isset($result["url"])){
            $url = $result["url"];
        }else{
            throw new ResourceTDTException("Can't find url of the XLS");
        }
		
        if(isset($result["sheet"])){
            $sheet = $result["sheet"];
        }else{
            throw new ResourceTDTException("Can't find sheet of the XLS");
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
          
        if(!file_exists($url)){
            throw new CouldNotGetDataTDTException($url);
        }
        try { 
            $objReader = PHPExcel_IOFactory::createReader('Excel2007');
            $objReader->setLoadSheetsOnly($sheet);
            $objPHPExcel = $objReader->load($url);

            $worksheet = $objPHPExcel->getSheetByName($sheet);
            foreach ($worksheet->getRowIterator() as $row) {
                $rowIndex = $row->getRowIndex();
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                if ($rowIndex == 1) {
                    foreach ($cellIterator as $cell) {
                        $columnIndex = $cell->columnIndexFromString($cell->getColumn());
                        $fieldhash[ $cell->getCalculatedValue() ] = $columnIndex;						
                    }
                }
                else {
                    $rowobject = new stdClass();
                    $keys = array_keys($fieldhash);
                    foreach ($cellIterator as $cell) {
                        $columnIndex = $cell->columnIndexFromString($cell->getColumn());
                        if (!is_null($cell)) {
                            $c = $keys[$columnIndex - 1];
                            if(sizeof($columns) == 0 || in_array($c,$columns)){
                                $rowobject->$c = $cell->getCalculatedValue();
                            }
                        }
                    }
                    if($PK == "") {
                        array_push($arrayOfRowObjects,$rowobject);   
                    } else {
                        if(!isset($arrayOfRowObjects[$rowobject->$PK])){
                            $arrayOfRowObjects[$rowobject->$PK] = $rowobject;
                        }
                    }
                }
            }
            
            $resultobject->object = $arrayOfRowObjects;
            return $resultobject;
        } catch( Exception $ex) {
            throw new CouldNotGetDataTDTException( $url );
        }
    }

    public function onDelete($package,$resource){
        DBQueries::deleteXLSResource($package, $resource);
    }

    public function onAdd($package_id,$resource_id){
        $this->evaluateXLSResource($resource_id);
        parent::evaluateColumns($this->columns,$this->PK,$resource_id);
    }

    private function evaluateXLSResource($resource_id){
        DBQueries::storeXLSResource($resource_id, $this->url, $this->sheet);
    }    
}
?>
