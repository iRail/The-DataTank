<?php
/**
 * This class handles a CSV file
 *
 * @package The-Datatank/model/resources/strategies
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */
include_once("model/resources/strategies/ATabularData.class.php");
class CSV extends ATabularData {

    public function onCall($package,$resource){

        /*
         * First retrieve the values for the generic fields of the CSV logic
         */
        $param = array(':package' => $package, ':resource' => $resource);
        $result = R::getAll(
            "select generic_resource.id as gen_res_id,generic_resource_csv.uri as uri, generic_resource_csv.columns as columns
             from package, generic_resource, generic_resource_csv
             where package.package_name=:package and generic_resource.resource_name=:resource
             and package.id=generic_resource.package_id 
             and generic_resource.id=generic_resource_csv.resource_id",
            $param
        );
        

        $gen_res_id = $result[0]["gen_res_id"];

        if(isset($result[0]["uri"])){
            $filename = $result[0]["uri"];
        }else{
            throw new ResourceTDTException("Can't find URI of the CSV");
        }

        $columns = array();
        
        // get the columns from the columns table
        $allowed_columns = R::getAll(
            "SELECT column_name, is_primary_key
                 FROM published_columns
                 WHERE generic_resource_id=:id",
            array(":id" => $gen_res_id)
        );
            
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
                        if($PK == ""){
                            array_push($arrayOfRowObjects,$rowobject);   
                        }else{
                            if(!isset($arrayOfRowObjects[$rowobject->$PK])){
                                $arrayOfRowObjects[$rowobject->$PK] = $rowobject;
                            }
                        }
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

    public function onDelete($package,$resource){
        $deleteCSVResource = R::exec(
            "DELETE FROM generic_resource_csv 
                     WHERE resource_id IN 
                           (SELECT generic_resource.id FROM generic_resource,package WHERE resource_name=:resource
                                                                                    and package_name=:package
                                                                                    and package.id=package_id)",
            array(":package" => $package, ":resource" => $resource)
        );
    }

    public function onAdd($package_id,$resource_id,$content){
        $this->evaluateCSVResource($resource_id,$content);
        parent::evaluateColumns($content["columns"],$content["PK"],$resource_id);
    }

    public function onUpdate($package,$resource,$content){
        // At the moment there's no request for foreign relationships between CSV files
        // Yet this could be perfectly possible!
    }
    

    private function evaluateCSVResource($resource_id,$content){
        $csvresource = R::dispense("generic_resource_csv");
        $csvresource->resource_id = $resource_id;
        $csvresource->uri = $content["uri"];
        R::store($csvresource);
    }    
}
?>
