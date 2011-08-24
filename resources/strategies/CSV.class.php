<?php
  /**
   * This class handles a CSV file
   *
   * @package The-Datatank/resources/strategies
   * @copyright (C) 2011 by iRail vzw/asbl
   * @license AGPLv3
   * @author Jan Vansteenlandt
   */

class CSV extends AResourceStrategy {

   
    public function __construct(){
	
    }

    public function call($module,$resource){

        /*
         * First retrieve the values for the generic fields of the CSV logic
         */

        R::setup(Config::$DB,Config::$DB_USER,Config::$DB_PASSWORD);
        $param = array(':module' => $module, ':resource' => $resource);
        $result = R::getAll(
            "select generic_resource_csv.uri as uri, generic_resource_csv.columns as columns
             from module, generic_resource, generic_resource_csv
             where module.module_name=:module and generic_resource.resource_name=:resource
             and module.id=generic_resource.module_id 
             and generic_resource.id=generic_resource_csv.resource_id",
             $param
        );

        if(isset($result[0]["uri"])){
            $filename = $result[0]["uri"];
        }else{
            throw new ResourceTDTException("Can't find URI of the CSV");
        }

        // if we no columns are defined the columns field, ""-value. 
        // Then we need to publish all the columns, by passing an empty array
        if($result[0]["columns"] != ""){
            $columns = explode(";",$result[0]["columns"]);
        }else{
            $columns = array();
        }
        
        
	$resultobject = new stdClass();
	$arrayOfRowObjects = array();
        $row = 0;
	  
	if(!file_exists($filename)){
	    throw new CouldNotGetDataTDTException($filename);
	}
	try{ 
	    if (($handle = fopen($filename, "r")) !== FALSE) {
                
		$fieldhash = array();	
		while ( ( $data = fgetcsv( $handle, 1000, "," ) ) !== FALSE ) {
		    if ( $row == 0 ) {
			for ( $i = 0 ; $i < sizeof($data) ; $i++ ) {
			    $fieldhash[ $data[$i] ] = $i;
			}
		    }
		    else {
			$rowobject    = new stdClass();
			$keys = array_keys($fieldhash);
			for ( $i = 0 ; $i < sizeof($keys) ; $i++ ) {
			    $c = $keys[$i];
                            if(sizeof($columns) == 0 || in_array($c,$columns)){
                                $rowobject->$c = $data[ $fieldhash[$c] ];
                            }
			}
			array_push($arrayOfRowObjects,$rowobject);
		    }
		    $row++;
		}
		fclose($handle);
	    }
	    else {
		throw new CouldNotGetDataTDTException( $filename );
	    }

	    $resultobject->object = $arrayOfRowObjects;
	    return $resultobject;
	}catch( Exception $ex) {
	    throw new CouldNotGetDataTDTException( $filename );
	}
    }
}
?>
