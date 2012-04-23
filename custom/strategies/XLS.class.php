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

class XLS extends ATabularData {

    public static $PAGE_SIZE = 2;

    public function documentCreateParameters(){
        $this->parameters["uri"] = "The path to the excel sheet (can be a url as well).";
        $this->parameters["sheet"] = "The sheet name of the excel";
        $this->parameters["named_range"] = "The named range of the excel";
        $this->parameters["cell_range"] = "Range of cells (i.e. A1:B10)";
        $this->parameters["PK"] = "The primary key for each row.";
        $this->parameters["has_header_row"] = "If the XLS file contains a header row with the column name, pass 1 as value, if not pass 0. Default value is 1.";
        $this->parameters["start_row"] = "The number of the row (rows start at number 1) at which the actual data starts; i.e. if the first two lines are comment lines, your start_row should be 3. Default is 1.";
        $this->parameters["columns"] = "Columns";
        return $this->parameters;	
    }
    
    public function documentCreateRequiredParameters(){
        return array("uri", "sheet");    
    }

    public function documentReadRequiredParameters(){
        return array();
    }
    
    public function documentReadParameters(){
        return array("startindex" => "The startrow of the datasource you want as a starting point of your response.",
                     "endindex" => "The endrow of the datasource you want in the response. Note that a startindex must be passed in order to use the endindex parameter.",
                     "page" => "The page of the datasource you want to access, paging is done on an internal parameter, currently set to " . XLS::$PAGE_SIZE ."."
        );
    }

    public function __construct() {
        if(Config::$PHPEXCEL_IOFACTORY_PATH!="") {
            if(!file_exists(Config::$PHPEXCEL_IOFACTORY_PATH)){
                throw new NotFoundTDTException("Could not include " . Config::$PHPEXCEL_IOFACTORY_PATH);
            } else {
                include_once(Config::$PHPEXCEL_IOFACTORY_PATH);
            }
        }
    }

    protected function isValid($package_id,$generic_resource_id) {
           
        if(Config::$PHPEXCEL_IOFACTORY_PATH!="") {
            if(!file_exists(Config::$PHPEXCEL_IOFACTORY_PATH)){
                throw new NotFoundTDTException("Could not include " . Config::$PHPEXCEL_IOFACTORY_PATH);
            } else {
                include_once(Config::$PHPEXCEL_IOFACTORY_PATH);
            }
        }else{
            throw new NotFoundTDTException("No path to the PHPExcel library was defined in the Config file.");
        }

        if(!isset($this->uri)){
            $this->throwException($package_id,$generic_resource_id, "Can't find uri of the XLS");
        }
		
        if(!isset($this->sheet)){
            $this->throwException($package_id,$generic_resource_id, "Can't find sheet of the XLS");
        }
	
        if (!isset($this->columns)) {
            $this->columns = array();
        }

        if (!isset($this->PK)) {
            $this->PK = "";
        }

        if (!isset($this->start_row)) {
            $this->start_row = 1;
        }
		
        if (!isset($this->has_header_row)) {
            $this->has_header_row = 1;
        }

        $uri = $this->uri;
        $sheet = $this->sheet;
        $columns = $this->columns;

        /**
         * if no header row is given, then the columns that are being passed should be 
         * int => something, int => something
         * if a header row is given however in the csv file, then we're going to extract those 
         * header fields and put them in our back-end as well.
         */
        
        if ($this->has_header_row == "0") {
            // no header row ? then columns must be passed
            if(empty($this->columns)){
                $this->throwException($package_id,$generic_resource_id,"Your array of columns must be an index => string hash array. Since no header row is specified in the resource CSV file.");
            }
            
            foreach ($this->columns as $index => $value) {
                if (!is_numeric($index)) {
                    $this->throwException($package_id,$generic_resource_id,"Your array of columns must be an index => string hash array.");
                }
            }

        } else {

            // if no column aliases have been passed, then fill the columns variable 
            if(empty($this->columns)){
                if (!is_dir("tmp")) {
                    mkdir("tmp");
                }
			
                $isUri = (substr($uri , 0, 4) == "http");
                if ($isUri) {				
                    $tmpFile = uniqid();
                    file_put_contents("tmp/" . $tmpFile, file_get_contents($uri));
                    $objPHPExcel = $this->loadExcel("tmp/" . $tmpFile,$this->getFileExtension($uri),$sheet);
                } else {
                    $objPHPExcel = $this->loadExcel($uri,$this->getFileExtension($uri),$sheet);				
                }
                    
                $worksheet = $objPHPExcel->getSheetByName($sheet);
                if (!isset($this->named_range) && !isset($this->cell_range)) {
                    foreach ($worksheet->getRowIterator() as $row) {
                        $rowIndex = $row->getRowIndex();
                        if ($rowIndex == $this->start_row) {
                            $cellIterator = $row->getCellIterator();
                            $cellIterator->setIterateOnlyExistingCells(false);
                            foreach ($cellIterator as $cell) {
                                if($cell->getCalculatedValue() != ""){
                                    $this->columns[$cell->getCalculatedValue()] = $cell->getCalculatedValue();
                                }
                            }
                        }
                    }
                } else {
                    if(isset($this->named_range)) {
                        $range = $worksheet->namedRangeToArray($this->named_range);
                    }
                    if(isset($this->cell_range)) {
                        $range = $worksheet->rangeToArray($this->cell_range);					
                    }
                    $rowIndex = 1;
                    foreach ($range as $row) {
                        if ($rowIndex == $this->start_row) {
                            foreach ($row as $cell) {
                                $this->columns[$cell] = $cell;
                            }
                        }
                        $rowIndex += 1;
                    }					
                }
                $objPHPExcel->disconnectWorksheets();
                unset($objPHPExcel);
                if ($isUri) {
                    unlink("tmp/" . $tmpFile);				
                }
            }
        }
        return true;
    }
	
    public function read(&$configObject,$package,$resource) {
       
         /**
         * Check if the possible read parameters are passed in a correct way
         */

        if(isset($this->startindex) && isset($this->endindex) && $this->endindex < $this->startindex){
            throw new ParameterTDTException("Your endindex cannot be smaller than your startindex.");
        }else if(isset($this->page) && (isset($this->startindex) || isset($this->endindex))){
            throw new ParameterTDTException("The usage of page and indexes are mutually exclusive.");
        }else if(isset($this->endindex) && !isset($this->startindex)){
            throw new ParameterTDTException("You cannot use endindex without a startindex.");
        }

        /**
         * convert page to startindex and endindex
         */
        if(isset($this->page)){
            $this->startindex = $this->page * XLS::$PAGE_SIZE - XLS::$PAGE_SIZE + 1;
            $this->endindex = $this->startindex + XLS::$PAGE_SIZE -1;
        }

        if(!isset($this->startindex)){
            $this->startindex = 1;
        }

        parent::read($configObject,$package,$resource);
        $uri = $configObject->uri;
        $sheet = $configObject->sheet;
        $has_header_row = $configObject->has_header_row;
        $start_row = $configObject->start_row;

        $columns = array();
        
        $PK = $configObject->PK;
            
        $columns = $configObject->columns;
        $resultobject = new stdClass();
        $arrayOfRowObjects = array();
        $row = 0;
          
        if (!is_dir("tmp")) {
            mkdir("tmp");
        }

        if(!isset($this->startindex)){
            $this->startindex = 1;
        }

        if(!isset($this->endindex)){
            // the documentation of php excel is broken, the usage of reading XLS-files is corrupt ... 
            $this->endindex = -1;
        }
        

        try { 
            $isUri = (substr($uri , 0, 4) == "http");
            if ($isUri) {						
                $tmpFile = uniqid();
                file_put_contents("tmp/" . $tmpFile, file_get_contents($uri));
				
                $objPHPExcel = $this->loadExcel("tmp/" . $tmpFile,$this->getFileExtension($uri),$sheet);
            } else {
                $objPHPExcel = $this->loadExcel($uri,$this->getFileExtension($uri),$sheet);			
            }
            
            $worksheet = $objPHPExcel->getSheetByName($sheet);

            if (($configObject->named_range == "") && ($configObject->cell_range == "")) {
                foreach ($worksheet->getRowIterator() as $row) {
                    $rowIndex = $row->getRowIndex();
                    if ($rowIndex >= $start_row) {
                        $cellIterator = $row->getCellIterator();
                        $cellIterator->setIterateOnlyExistingCells(false);

                        if ($rowIndex == $start_row && $has_header_row == "1") {
                            foreach ($cellIterator as $cell) {
                                if(!is_null($cell) && $cell->getCalculatedValue() != ""){    
                                    $columnIndex = $cell->columnIndexFromString($cell->getColumn());
                                    $fieldhash[ $cell->getCalculatedValue() ] = $columnIndex;
                                }
                            }
                        } else if($start_row + $this->startindex <= $rowIndex
                                  && ($start_row + $this->endindex >= $rowIndex || $this->endindex == -1)){
                            $rowobject = new stdClass();
                            $keys = array_keys($fieldhash);
                            foreach ($cellIterator as $cell) {
                                $columnIndex = $cell->columnIndexFromString($cell->getColumn());
                                if (!is_null($cell) && isset($keys[$columnIndex-1]) ) {
                                    $c = $keys[$columnIndex - 1];
                                    if(array_key_exists($c,$columns)){
                                        $rowobject->$columns[$c] = $cell->getCalculatedValue();
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
                        }else if($start_row + $this->endindex < $rowIndex && $this->endindex != -1){
                            if(isset($this->page)){
                                $page = $this->page + 1;
                                header("Link: ". Config::$HOSTNAME . Config::$SUBDIR . $package . "/" . $resource .".about?page=".$page);
                            }else{
                                $start = $start_row+$this->endindex;
                                header("Link: " . Config::$HOSTNAME . Config::$SUBDIR . $package . "/" . $resource .".about?beginindex=".$start);
                            }
                            break;
                        }
                    }
                }
            } else {
                if($configObject->named_range != "") {
                    $range = $worksheet->namedRangeToArray($configObject->named_range);
                }
                if($configObject->cell_range != "") {
                    $range = $worksheet->rangeToArray($configObject->cell_range);					
                }
                $rowIndex = 1;
                foreach ($range as $row) {
                    if ($rowIndex >= $start_row) {			
                        if ($rowIndex == $start_row) {
                            if ($has_header_row == 0) {
                                $columnIndex = 1;
                                foreach ($row as $cell) {
                                    $fieldhash[ $columnIndex - 1 ] = $columnIndex;
                                    $columnIndex += 1;
                                }
                            } else {
                                $columnIndex = 1;
                                foreach ($row as $cell) {
                                    $fieldhash[ $cell ] = $columnIndex;
                                    $columnIndex += 1;
                                }
                            }
                        } 
								//$start_row + $this->startindex <= $rowIndex
	//                   && ($start_row + $this->endindex >= $rowIndex || $this->endindex == -1)
                        if ($has_header_row == 0 or 
                            ($rowIndex >= $start_row + $this->startindex && ($start_row + $this->endindex >= $rowIndex || $this->endindex == -1)
                                                   )) {
                            $rowobject = new stdClass();
                            $keys = array_keys($fieldhash);
                            $columnIndex = 1;
                            foreach ($row as $cell) {
                                $c = $keys[$columnIndex - 1];
                                if(array_key_exists($c,$columns)){
                                    $rowobject->$columns[$c] = $cell;
                                }								
                                $columnIndex += 1;
                            }
                            if($PK == "") {
                                array_push($arrayOfRowObjects,$rowobject);   
                            } else {
                                if(!isset($arrayOfRowObjects[$rowobject->$PK])){
                                    $arrayOfRowObjects[$rowobject->$PK] = $rowobject;
                                }
                            }							
                        }else if($start_row + $this->endindex < $rowIndex && $this->endindex != -1){
                            if(isset($this->page)){
                                $page = $this->page + 1;
                                header("Link: ". Config::$HOSTNAME . Config::$SUBDIR . $package . "/" . $resource .".about?page=".$page);
                            }else{
                                $start = $start_row+$this->endindex;
                                header("Link: " . Config::$HOSTNAME . Config::$SUBDIR . $package . "/" . $resource .".about?beginindex=".$start);
                            }
                            break;
                        }
                    }
                    $rowIndex += 1;
                }
            }
            
            $objPHPExcel->disconnectWorksheets();
            unset($objPHPExcel);
            if ($isUri) {									
                unlink("tmp/" . $tmpFile);
            }
			
            return $arrayOfRowObjects;
        } catch( Exception $ex) {
            throw new CouldNotGetDataTDTException( $uri );
        }
    }
	
    private function getFileExtension($fileName)
    {
        return strtolower(substr(strrchr($fileName,'.'),1));
    }	
	
    private function loadExcel($xlsFile,$type,$sheet) {
        if($type == "xls") {
            $objReader = PHPExcel_IOFactory::createReader('Excel5');			
        }else if($type == "xlsx") {
            $objReader = PHPExcel_IOFactory::createReader('Excel2007');
        }else{
            throw new CouldNotGetDataTDTException("Wrong datasource, accepted datasources are .xls or .xlsx files.");
        }
        
        $objReader->setReadDataOnly(true);
        $objReader->setLoadSheetsOnly($sheet);
        return $objReader->load($xlsFile);	
    }
}
?>
