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

    public function documentCreateParameters(){
        $this->parameters["url"] = "The path to the excel sheet (can be a url as well).";
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
        return array("url", "sheet");    
    }

    public function documentReadRequiredParameters(){
        return array();
    }
    
    public function documentReadParameters(){
        return array();
    }

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

    protected function isValid($package_id,$generic_resource_id) {
        if(!isset($this->url)){
			$this->throwException($package_id,$generic_resource_id, "Can't find url of the XLS");
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

		$url = $this->url;
		$sheet = $this->sheet;
		$columns = $this->columns;
        
        // has_header_row should be either 1 or 0
        if($this->has_header_row != 0 && $this->has_header_row != 1){
            $this->throwException($package_id,$generic_resource_id, "Header row should be either 1 or 0.");
        }

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
			
				$isUrl = (substr($url , 0, 4) == "http");
				if ($isUrl) {				
					$tmpFile = com_create_guid();
					$tmpFile = substr($tmpFile, 1, strlen($tmpFile) - 2);
					file_put_contents("tmp/" . $tmpFile, file_get_contents($url));
					
					$objPHPExcel = $this->loadExcel("tmp/" . $tmpFile,$this->getFileExtension($url),$sheet);
				} else {
					$objPHPExcel = $this->loadExcel($url,$this->getFileExtension($url),$sheet);				
				}
				
				$worksheet = $objPHPExcel->getSheetByName($sheet);
				
				if (!isset($this->named_range) && !isset($this->cell_range)) {
					foreach ($worksheet->getRowIterator() as $row) {
						$rowIndex = $row->getRowIndex();
						if ($rowIndex == $this->start_row) {
							$cellIterator = $row->getCellIterator();
							$cellIterator->setIterateOnlyExistingCells(false);
							foreach ($cellIterator as $cell) {
								$this->columns[$cell->getCalculatedValue()] = $cell->getCalculatedValue();
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
				if ($isUrl) {
					unlink("tmp/" . $tmpFile);				
				}
			}
        }
        return true;
    }
	
    public function read(&$configObject) {
       
		parent::read($configObject);
        $url = $configObject->url;
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

        try { 
			$isUrl = (substr($url , 0, 4) == "http");
			if ($isUrl) {						
				$tmpFile = com_create_guid();
				$tmpFile = substr($tmpFile, 1, strlen($tmpFile) - 2);
				file_put_contents("tmp/" . $tmpFile, file_get_contents($url));
				
				$objPHPExcel = $this->loadExcel("tmp/" . $tmpFile,$this->getFileExtension($url),$sheet);
			} else {
				$objPHPExcel = $this->loadExcel($url,$this->getFileExtension($url),$sheet);			
			}
            
			$worksheet = $objPHPExcel->getSheetByName($sheet);
			
			if (!isset($configObject->named_range) && !isset($configObject->cell_range)) {
				foreach ($worksheet->getRowIterator() as $row) {
					$rowIndex = $row->getRowIndex();
					if ($rowIndex >= $start_row) {
						$cellIterator = $row->getCellIterator();
						$cellIterator->setIterateOnlyExistingCells(false);
						if ($rowIndex == $start_row && $has_header_row == "1") {
							foreach ($cellIterator as $cell) {
								$columnIndex = $cell->columnIndexFromString($cell->getColumn());
								$fieldhash[ $cell->getCalculatedValue() ] = $columnIndex;						
							}
						} else {
							$rowobject = new stdClass();
							$keys = array_keys($fieldhash);
							foreach ($cellIterator as $cell) {
								$columnIndex = $cell->columnIndexFromString($cell->getColumn());
								if (!is_null($cell)) {
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
						if ($has_header_row == 0 or ($rowIndex > $start_row and $has_header_row != 0)) {
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
						}
					}
					$rowIndex += 1;
				}
			}
            
			$objPHPExcel->disconnectWorksheets();
			unset($objPHPExcel);
			if ($isUrl) {									
				unlink("tmp/" . $tmpFile);
			}
			
            return $arrayOfRowObjects;
        } catch( Exception $ex) {
            throw new CouldNotGetDataTDTException( $url );
        }
    }
	
	private function getFileExtension($fileName)
	{
	  return substr(strrchr($fileName,'.'),1);
	}	
	
	private function loadExcel($xlsFile,$type,$sheet) {
		if($type == "xls") {
			$objReader = PHPExcel_IOFactory::createReader('Excel5');			
		}
		if($type == "xlsx") {
			$objReader = PHPExcel_IOFactory::createReader('Excel2007');
		}
		$objReader->setReadDataOnly(true);
		$objReader->setLoadSheetsOnly($sheet);
		return $objReader->load($xlsFile);	
	}
}
?>