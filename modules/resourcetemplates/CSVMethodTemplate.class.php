<?php

require_once("error/Exceptions.class.php");
require_once("resources/AResource.class.php");

class CLASSNAME extends AResource {

	private $filename;

    public function __construct(){
		parent::__construct("CLASSNAME");
		
    }

	public static function getRequiredParameters(){
		return array("filename");
    }

     public static function getParameters(){
		  return array("filename" => "Name of the csv-file");
		  //TODO add additional arguments so that columnames can be passed along as a parameter
		  //i.e. if you have a csv with columnames age,name,salary, you need to add these
		  //as a parameter so a user can pass these along in order to get those information fields
     }

     public static function getDoc(){
		  // TODO create own documentation
		return "This is a method which reads a CSV file.";
     }
     
     public function setParameter($key,$val){
		if($key=="filename"){
			 //TODO add your assigning logic
		} 
     }

	public static function getAllowedPrintMethods(){
		 return array(); //TODO add allowed printmethods
	}

     public function call(){
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
