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
include_once("includes/proj4php/proj4php.php");

class SHP extends ATabularData {

    public function documentCreateParameters(){
        return array("uri" => "The path to the shape file (can be a url).",
                     "EPSG" => "EPSG coordinate system code.",
                     "columns" => "The columns that are to be published.",
                     "PK" => "The primary key for each row.",
        );
    }
    
    public function documentCreateRequiredParameters(){
        return array("uri");    
    }

    public function documentReadRequiredParameters(){
        return array();
    }
    
    public function documentReadParameters(){
        return array();
    }

    protected function isValid($package_id,$generic_resource_id) {
        if(!isset($this->uri)){
			$this->throwException($package_id,$generic_resource_id, "Can't find uri of the Shape file");
        }
		
        if (!isset($this->columns)) {
            $this->columns = array();
        }

        if (!isset($this->PK)) {
            $this->PK = "";
        }
		
        if (!isset($this->EPSG)) {
            $this->EPSG = "";
        }		

		$uri = $this->uri;
		$columns = $this->columns;

		if (!is_dir("tmp")) {
			mkdir("tmp");
		}

		if(empty($this->columns)){ 
			$options = array('noparts' => false);
			$isUrl = (substr($uri , 0, 4) == "http");
			if ($isUrl) {
				$tmpFile = uniqid();
				file_put_contents("tmp/" . $tmpFile . ".shp", file_get_contents(substr($uri, 0, strlen($uri) - 4) . ".shp"));
				file_put_contents("tmp/" . $tmpFile . ".dbf", file_get_contents(substr($uri, 0, strlen($uri) - 4) . ".dbf"));
				file_put_contents("tmp/" . $tmpFile . ".shx", file_get_contents(substr($uri, 0, strlen($uri) - 4) . ".shx"));

				$shp = new ShapeFile("tmp/" . $tmpFile . ".shp", $options); // along this file the class will use file.shx and file.dbf
			} else {
				$shp = new ShapeFile($uri, $options); // along this file the class will use file.shx and file.dbf			
			}

			$record = $shp->getNext();
			// read meta data
                        if($record == false){
                            exit();
                        }
                        
			$dbf_data = $record->getDbfData();
			foreach ($dbf_data as $property => $value) {
				$property = strtolower($property);
				$this->columns[$property] = $property;
			}

			$shp_data = $record->getShpData();
			if(isset($shp_data['parts'])) {
				$this->columns["coords"] = "coords";
			}
			if(isset($shp_data['x'])) {
				$this->columns["lat"] = "lat";
				$this->columns["long"] = "long";
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
       
        if(isset($configObject->uri)){
            $uri = $configObject->uri;
        }else{
            throw new ResourceTDTException("Can't find uri of the Shape file");
        }
		
        $columns = array();
        
        $PK = $configObject->PK;

        $EPSG = $configObject->EPSG;
            
        $columns = $configObject->columns;
        
        $resultobject = new stdClass();
        $arrayOfRowObjects = array();
        $row = 0;
          	
		if (!is_dir("tmp")) {
			mkdir("tmp");
		}

		try { 
			$options = array('noparts' => false);
			$isUrl = (substr($uri , 0, 4) == "http");
			if ($isUrl) {	
				$tmpFile = uniqid();
				file_put_contents("tmp/" . $tmpFile . ".shp", file_get_contents(substr($uri, 0, strlen($uri) - 4) . ".shp"));
				file_put_contents("tmp/" . $tmpFile . ".dbf", file_get_contents(substr($uri, 0, strlen($uri) - 4) . ".dbf"));
				file_put_contents("tmp/" . $tmpFile . ".shx", file_get_contents(substr($uri, 0, strlen($uri) - 4) . ".shx"));

				$shp = new ShapeFile("tmp/" . $tmpFile . ".shp", $options); // along this file the class will use file.shx and file.dbf
			} else {
				$shp = new ShapeFile($uri, $options); // along this file the class will use file.shx and file.dbf						
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
				
				if(array_key_exists("coords",$columns) || array_key_exists("lat",$columns)) {
					// read shape data
					$shp_data = $record->getShpData();
					if ($EPSG != "") {
						$proj4 = new Proj4php();
						$projSrc = new Proj4phpProj('EPSG:'.$EPSG,$proj4);
						$projDest = new Proj4phpProj('EPSG:4326',$proj4);
					}

					if(isset($shp_data['parts'])) {
						foreach ($shp_data['parts'] as $part) {
							$coords = array();
							foreach ($part['points'] as $point) {
								$x = $point['x'];
								$y = $point['y'];
								if ($EPSG != "") {
									$pointSrc = new proj4phpPoint($x,$y);
									$pointDest = $proj4->transform($projSrc,$projDest,$pointSrc);
									$x = $pointDest->x;
									$y = $pointDest->y;
								}
								//$rowobject->long = ;	
								$coords[] = $y.','.$x;
							}
							$rowobject->coords = implode(';', $coords);
						}
					}
					if(isset($shp_data['x'])) {
						$x = $shp_data['x'];
						$y = $shp_data['y'];

						if ($EPSG != "") {
							$pointSrc = new proj4phpPoint($x,$y);
							$pointDest = $proj4->transform($projSrc,$projDest,$pointSrc);
							$x = $pointDest->x;
							$y = $pointDest->y;
						}

						$rowobject->long = $x;
						$rowobject->lat = $y;
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
            throw new CouldNotGetDataTDTException( $uri );
        }
    }	
}
?>
