<?php
/**
 * This class handles a SHP file
 *
 * @package The-Datatank/custom/strategies
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Lieven Janssen
 */
include_once("custom/strategies/ATabularData.class.php");
include_once("includes/ShapeFile.inc.php");

class SHP extends ATabularData {

    public function documentCreateParameters(){
        return array("url" => "The path to the shape file (can be a url).",
                     "columns" => "The columns that are to be published.",
                     "PK" => "The primary key for each row.",
        );
    }
    
    public function documentCreateRequiredParameters(){
        return array("url");    
    }

    public function documentReadRequiredParameters(){
        return array();
    }
    
    public function documentReadParameters(){
        return array();
    }

    protected function isValid($package_id,$generic_resource_id) {
        if(!isset($this->url)){
			$this->throwException($package_id,$generic_resource_id, "Can't find url of the XLS");
        }
		
        if (!isset($this->columns)) {
            $this->columns = array();
        }

        if (!isset($this->PK)) {
            $this->PK = "";
        }

		$url = $this->url;
		$columns = $this->columns;
        
		if(empty($this->columns)){ 
			$options = array('noparts' => false);
			$isUrl = (substr($url , 0, 4) == "http");
			if ($isUrl) {
				$tmpFile = com_create_guid();
				$tmpFile = substr($tmpFile, 1, strlen($tmpFile) - 2);
				file_put_contents("tmp/" . $tmpFile . ".shp", file_get_contents(substr($url, 0, strlen($url) - 4) . ".shp"));
				file_put_contents("tmp/" . $tmpFile . ".dbf", file_get_contents(substr($url, 0, strlen($url) - 4) . ".dbf"));
				file_put_contents("tmp/" . $tmpFile . ".shx", file_get_contents(substr($url, 0, strlen($url) - 4) . ".shx"));

				$shp = new ShapeFile("tmp/" . $tmpFile . ".shp", $options); // along this file the class will use file.shx and file.dbf
			} else {
				$shp = new ShapeFile($url, $options); // along this file the class will use file.shx and file.dbf			
			}

			while ($record = $shp->getNext()) {
				// read meta data
				$dbf_data = $record->getDbfData();
				foreach ($dbf_data as $property => $value) {
					$property = strtolower($property);
					$this->columns[$property] = $property;
				}
				
				$this->columns["coords"] = "coords";
			}
			
            unset($shp);
			if ($isUrl) {
				unlink("tmp/" . $tmpFile . ".shp");
				unlink("tmp/" . $tmpFile . ".dbf");
				unlink("tmp/" . $tmpFile . ".shx");
			}
		}
        return true;
    }	
	
    public function read(&$configObject) {
		set_time_limit(1000);
	
		parent::read($configObject);
       
        if(isset($configObject->url)){
            $url = $configObject->url;
        }else{
            throw new ResourceTDTException("Can't find url of the Shape file");
        }
		
        $columns = array();
        
        $PK = $configObject->PK;
            
        $columns = $configObject->columns;
        
        $resultobject = new stdClass();
        $arrayOfRowObjects = array();
        $row = 0;
          	
        try { 
			$options = array('noparts' => false);
			$isUrl = (substr($url , 0, 4) == "http");
			if ($isUrl) {	
				$tmpFile = com_create_guid();
				$tmpFile = substr($tmpFile, 1, strlen($tmpFile) - 2);
				file_put_contents("tmp/" . $tmpFile . ".shp", file_get_contents(substr($url, 0, strlen($url) - 4) . ".shp"));
				file_put_contents("tmp/" . $tmpFile . ".dbf", file_get_contents(substr($url, 0, strlen($url) - 4) . ".dbf"));
				file_put_contents("tmp/" . $tmpFile . ".shx", file_get_contents(substr($url, 0, strlen($url) - 4) . ".shx"));

				$shp = new ShapeFile("tmp/" . $tmpFile . ".shp", $options); // along this file the class will use file.shx and file.dbf
			} else {
				$shp = new ShapeFile($url, $options); // along this file the class will use file.shx and file.dbf						
			}

			while ($record = $shp->getNext()) {
				// read meta data
				$rowobject = new stdClass();	
				$dbf_data = $record->getDbfData();
				foreach ($dbf_data as $property => $value) {
					$property = strtolower($property);
					if(array_key_exists($property,$columns)) {
						$rowobject->$property = trim($value);
					}
				}	
				
				if(array_key_exists("coords",$columns)) {
					// read shape data
					$shp_data = $record->getShpData();
					if(isset($shp_data['parts'])) {
						foreach ($shp_data['parts'] as $part) {
							$coords = array();
							foreach ($part['points'] as $point) {
								$coords[] = round($point['y'],2).','.round($point['x'],2);
							}
							$rowobject->coords = implode(';', $coords);
						}
					}
					if(isset($shp_data['x'])) {
						$rowobject->coords = round($shp_data['y'],2).','.round($shp_data['x'],2);					
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
            
            unset($shp);
			if ($isUrl) {			
				unlink("tmp/" . $tmpFile . ".shp");
				unlink("tmp/" . $tmpFile . ".dbf");
				unlink("tmp/" . $tmpFile . ".shx");
			}
			return $arrayOfRowObjects;
        } catch( Exception $ex) {
            throw new CouldNotGetDataTDTException( $url );
        }
    }	
}
?>