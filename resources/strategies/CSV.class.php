<?php
  /**
   * This handles a CSV file
   *
   * @package The-Datatank/resources/strategies
   * @copyright (C) 2011 by iRail vzw/asbl
   * @license AGPLv3
   * @author Jan Vansteenlandt
   */

class CSV extends AResourceStrategy {

    private $filename;
    private $columns = array();

    public function fillInGenericParameters($filename, array $columns){
	$this->filename = $filename;
	$this->columns = $columns;
    }

    public function __construct(){
	
    }

    public function call(){
	//TODO implement the columns logic, so that if the columns array is empty, all
	// columns are passed along with the result, and if it's filled with names,
	// only those columns can join in.
	// generic CSV logic
	$resultobject = new stdClass();
	$arrayOfRowObjects = array();
        $row = 0;
	  
	if(!file_exists($this->filename)){
	    throw new CouldNotGetDataTDTException($this->filename);
	}
	try{ 
	    if (($handle = fopen($this->filename, "r")) !== FALSE) {
		// the first line of the file contains the columnheadings
		// the $arrayOfRowObjectsata contains an indexed array, not an associative one! 
		// So we're gonna make it when reading the first line. Also we're only going to include the columns that 
		// are passed along with the parameters. so i.e. ? filename =hello &age & id &address, only age, id and address will be extracted from every line . //
		//You'll have to pass along the allowed columns in the parameters array !!!!( see function getParameters() ) 
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
			    $rowobject->$c = $data[ $fieldhash[$c] ];
			}
			$arrayOfRowObjects[] = $rowobject;
		    }
		    $row++;
		}
		fclose($handle);
	    }
	    else {
		throw new CouldNotGetDataTDTException( $this->filename );
	    }

	    $resultobject->object = $arrayOfRowObjects;
	    return $resultobject;
	}catch( Exception $ex) {
	    throw new CouldNotGetDataTDTException( $this->filename );
	}
    }
}
?>
