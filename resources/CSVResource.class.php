<?php

  /**
   *This file contains the CSVResource.
   *@author Jan Vansteenlandt <jan@iRail.be>
   */


require_once("error/Exceptions.class.php");
require_once("AResource.class.php");

class CSVResource extends GenericResource {

    private $filename; // name of the .csv to address
    
    private fetchFields(){
	// fetch logic
	// $uniqueKeyForSpecificRow = "dummy";
	// connect with db and fetch the fields needed
    }

    public function call(){

	// fetch the necessary fields from the database table representing 
	// CSV resources
	self::fetchFields();

	// generic CSV logic
	$b = new stdClass();
	$d = array();
        $row = 0;
	  
	if(!file_exists($this->filename)){
	    throw new CouldNotGetDataTDTException($this->filename);
	}
	try{ 
	    if (($handle = fopen($this->filename, "r")) !== FALSE) {
		// the first line of the file contains the columnheadings
		// the $data contains an indexed array, not an associative one! 
		// So we're gonna make it when reading the first line. Also we're only going to include the columns that 
		// are passed along with the parameters. so i.e. ? filename =hello &age & id &address, only age, id and address will be extracted from every line . //
		//You'll have to pass along the allowed columns in the parameters array !!!!( see function getParameters() ) 
		$fieldhash = array();	
		while ( ( $data = fgetcsv( $handle, 1000, "," ) ) !== FALSE ) {
		    if ( $row == 0 ) {
			for ( $i = 0 ; $i < sizeof($data) ; $i++ ) {
			    if ( isset( $_GET[ $data[$i] ] ) ) {
				$fieldhash[ $data[$i] ] = $i;
			    }
			}
		    }
		    else {
			$r    = new stdClass();
			$keys = array_keys($fieldhash);
			for ( $i = 0 ; $i < sizeof($keys) ; $i++ ) {
			    $c = $keys[$i];
			    $r->$c = $data[ $fieldhash[$c] ];
			}
			$d[] = $r;
		    }
		    $row++;
		}
		fclose($handle);
	    }
	    else {
		throw new CouldNotGetDataTDTException( $this->filename );
	    }

	    $b->object = $d;
	    return $b;
	}catch( Exception $ex) { 
	    // file kon nie geopend worden,of er verliep iets fout tijdens het lezen van de file 
	    throw new CouldNotGetDataTDTException( $this->filename );
	}
    }
}
?>
