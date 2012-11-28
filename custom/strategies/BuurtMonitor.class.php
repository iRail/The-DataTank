<?php

/**
 * This class handles an XML file
 *
 * @package The-Datatank/custom/strategies
 * @copyright (C) 2011 by Digipolis
 * @license AGPLv3
 * @author Dries Droesbeke
 */
include_once ("custom/strategies/ATabularData.class.php");
include_once ("aspects/logging/BacklogLogger.class.php");
class BuurtMonitor extends ATabularData {
	
	/**
	 * The parameters returned are required to make this strategy work.
	 *
	 * @return array with parameter => documentation pairs
	 */
	public function documentCreateRequiredParameters() {
		return array (
				"uri",
				"guid" 
		);
	}
	
	/**
	 * The parameters ( array keys ) returned all of the parameters that can be
	 * used to create a strategy.
	 *
	 * @return array with parameter => documentation pairs
	 */
	public function documentCreateParameters() {
		$this->parameters ["uri"] = "The uri to the WSDL file";
		$this->parameters ["guid"] = "The guid of the swing report";
		return $this->parameters;
	}
	
	/**
	 * Returns an array with parameter => documentation pairs that can be used
	 * to read a CSV resource.
	 *
	 * @return array with parameter => documentation pairs
	 */
	public function documentReadParameters() {
		return array ();
	}
	
	/**
	 * Read a resource
	 *
	 * @param $configObject The
	 *        	configuration object containing all of the parameters
	 *        	necessary to read the resource.
	 * @param $package The
	 *        	package name of the resource
	 * @param $resource The
	 *        	resource name of the resource
	 * @return $mixed An object created with fields of a XML file.
	 */
	public function read(&$configObject, $package, $resource) {
		parent::read ( $configObject, $package, $resource );
		
		$arrayOfRowObjects = array ();
		
		try {
			
			// get data
			$arrayOfRowObjects = $this->getSwingData ( array (
					"uri" => $configObject->uri,
					"guid" => $configObject->guid,
					"columns" => $configObject->columns 
			) );
			
			return $arrayOfRowObjects;
		} catch ( Exception $ex ) {
			throw new CouldNotGetDataTDTException ( $configObject->uri );
		}
	}
	protected function isValid($package_id, $generic_resource_id) {
		$arrayOfRowObjects = $this->getSwingData ( array (
				"uri" => $this->uri,
				"guid" => $this->guid,
				"columns" => (isset ( $this->columns ) ? $this->columns : null) 
		) );
		
		if (count ( $arrayOfRowObjects ) == 0) {
			return false;
		}
		$arr_columns = get_object_vars ( $arrayOfRowObjects [0] );
		
		if (! isset ( $this->columns )) {
			$this->columns = array ();
			foreach ( $arr_columns as $key => $value ) {
				$this->columns [] = $key;
			}
		}
		
		if (! isset ( $this->column_aliases )) {
			$this->column_aliases = array ();
		}
		
		if (! isset ( $this->PK )) {
			$this->PK = "";
		}
		return true;
	}
	protected function getSwingData($arr_config) {
		$uri_endpoint = $arr_config ['uri'];
		$guid = $arr_config ['guid'];
		
		$client = new SoapClient ( $uri_endpoint );
		$obj = new stdClass ();
		$obj->guid = $guid; // basic
		$obj_result = $client->SelectionXmlByGuid ( $obj );
		$str_xml = $obj_result->SelectionXmlByGuidResult;
		
		/*
		 * $filename = "data_2dim.xml"; $handle = fopen ( $filename, "r" );
		 * $str_xml = fread ( $handle, filesize ( $filename ) ); fclose (
		 * $handle );
		 */
		$xml = simplexml_load_string ( $str_xml );
		
		// get the dimensions
		$arr_dimensions = array (); // hierarchy of all dimensions with values
		                            // with name
		                            // index
		$arr_dimension_keys = array (); // hierarchy of all dimensions with values
		                                // with
		                                // key index
		$arr_dimension_all_code_names = array (); // array with keymapping between
		                                          // code
		                                          // and
		                                          // name
		
		$arr_dimension_all_code_columnkey = array (); // array with keymapping
		                                              // between code and column name
		
		foreach ( $xml->xpath ( "/result/dimensions/dimension" ) as $obj_node ) {
			$arr_attributes = ( array ) $obj_node->attributes ();
			$code = $arr_attributes ["@attributes"] ['code'];
			$key_name = strtolower ( ( string ) $obj_node->name );
			$arr_dimensions [$key_name] ['name'] = ( string ) $obj_node->name;
			$arr_dimensions [$key_name] ['name_key'] = $key_name;
			foreach ( $obj_node->member as $obj_dimitem ) {
				$arr_attributes = ( array ) $obj_dimitem->attributes ();
				$code = $arr_attributes ["@attributes"] ['code'];
				$arr_dimensions [$key_name] ['values'] [$code] = ( string ) $obj_dimitem->name;
				$arr_dimension_all_code_names [$code] = ( string ) $obj_dimitem->name;
				$arr_dimension_all_code_columnkey [$code] = $arr_dimensions [$key_name] ['name_key'];
			}
		}
		$arr_dimension_keys = array_values ( $arr_dimensions );
		
		// get the dimensions
		$arr_collection = array ();
		$arr_headermapping = array (); // keeps track of cellid and
		                               // multidimensional
		                               
		// get the data
		$int_counter_row = 1;
		$int_counter_element = 0;
		
		$columnname_previous = "";
		
		$arr_xmlrows = $xml->xpath ( "/result/data/table/row" );
		$int_number_of_rows = count ( $arr_xmlrows );
		foreach ( $arr_xmlrows as $obj_xmlrow ) {
			$obj_data = new StdClass ();
			switch (true) {
				case $int_counter_row == 1 :
					$int_counter_headerleft = 0;
					$int_counter_element = 0;
					foreach ( $obj_xmlrow->children () as $obj_column ) {
						$arr_attributes = null;
						$code = null;
						if (isset ( $obj_column->member )) {
							$arr_attributes = ( array ) $obj_column->member->attributes ();
							$code = $arr_attributes ['@attributes'] ['code'];
						}
						if (isset ( $code )) {
							$arr_headermapping [1] [$int_counter_element] = $arr_dimension_all_code_names [$code];
						} else {
							$arr_headermapping [1] [$int_counter_element] = "";
							$int_counter_headerleft ++;
						}
						
						$int_counter_element ++;
					}
					break;
					break;
				case $int_counter_row == 2 && $int_counter_headerleft == 1 :
					$int_counter_element = 0;
					foreach ( $obj_xmlrow->children () as $obj_column ) {
						$arr_headermapping [2] [$int_counter_element] = ( string ) $obj_column;
						$int_counter_element ++;
					}
					break;
				default :
					
					// read each datarecord into $arr_row_data
					$arr_row_data = array ();
					$int_counter_element = 0;
					$arr_header_codes = array ();
					foreach ( $obj_xmlrow->children () as $obj_column ) {
						switch (strtolower ( $obj_column->getName () )) {
							case "header" :
								$arr_attributes = null;
								if (isset ( $obj_column->member )) {
									$arr_attributes = ( array ) $obj_column->member->attributes ();
								}
								$arr_header_codes [] = $arr_attributes ['@attributes'] ['code'];
								
								break;
							case "cell" :
								$arr_attributes="";
								$arr_attributes = $obj_column->attributes ();
								if(isset($arr_attributes['svt'])){
									if($arr_attributes['svt'] == "nvt"){
										$arr_row_data [$int_counter_element] = "-";
									}elseif($arr_attributes['svt'] == "nt"){
										$arr_row_data [$int_counter_element] = "x";
									}elseif($arr_attributes['svt'] == "toga"){
										$arr_row_data [$int_counter_element] = "?";
									}else{
										$arr_row_data [$int_counter_element] = ( string ) $obj_column;
									}
									
								}else{
									$arr_row_data [$int_counter_element] = ( string ) $obj_column;
								}
								
								
								break;
						}
						$int_counter_element ++;
					}
					
					if (count ( $arr_dimensions ) > 2) {
						if ($int_counter_headerleft != 1) {
							// multicolumn header layer on the left
							
							/*
							 * foreach($arr_header_codes as $index => $code ){
							 * $columnname =
							 * $arr_dimension_all_code_columnkey[$code];
							 * $obj_data->$columnname =
							 * $arr_dimension_all_code_names
							 * [$arr_header_codes[$index]]; } foreach (
							 * $arr_headermapping [1] as $index => $value ) { if
							 * ($value != "") { $columnname =
							 * "cell_".strtolower($value);
							 * $obj_data->$columnname = $arr_row_data[$index]; }
							 * }
							 */
							
							/*remap */

							
							$columnname_first = $arr_header_codes [0]; // subject
							$columnname_second = $arr_header_codes [1]; // year
							
							if (($columnname_previous != "") && (($columnname_first != $columnname_previous) || $int_number_of_rows == $int_counter_row)) {
								// TODO add check for last row
								
								if ($int_number_of_rows == $int_counter_row) {
									$columnname_previous = $columnname_first;
									foreach ( $arr_headermapping [1] as $index => $value ) {
										if ($value != "") {
											$arr_data_tmp [$columnname_second] [$value] = $arr_row_data [$index];
										}
									}
								}
								
								foreach ( $arr_dimension_keys [0] ['values'] as $key => $code ) {
									$obj_data_tmp = new stdClass ();
									// gebied
									$columnname = $arr_dimension_keys [0] ['name_key'];
									$obj_data_tmp->$columnname = $code;
									// onderwerk
									$columnname = $arr_dimension_all_code_columnkey [$columnname_first];
									$obj_data_tmp->$columnname = $arr_dimension_all_code_names [$columnname_previous];
									
									foreach ( $arr_data_tmp as $k => $arr_value ) {
										$columnname = "data_" . $arr_dimension_all_code_names [$k];
										
										$value = $arr_value [$code];
										if(is_null($value)|| $value == ""){
											$value = 0;
										}
										$value = str_replace(".", ",",$value);
										
										
										$obj_data_tmp->$columnname = $value;
									}
									$arr_collection [] = $obj_data_tmp;
								}
								$arr_data_tmp = array ();
							}
							
							$columnname_previous = $columnname_first;
							foreach ( $arr_headermapping [1] as $index => $value ) {
								if ($value != "") {
									$arr_data_tmp [$columnname_second] [$value] = $arr_row_data [$index];
								}
							}
							
							/* remap */
							
							// $arr_collection [] = $obj_data;
						} else {
							// 2layer columns on top
							
							// firstcolumn
							$columnname = $arr_dimension_keys [0] ['name_key'];
							if($arr_header_codes [0] <> ""){
							$obj_data->$columnname = $arr_dimension_all_code_names [$arr_header_codes [0]];
							}else{
								//total column has been reached
								break;
							}
							
							// multidimensional array
							$columnname_1 = $arr_dimension_keys [1] ['name_key'];
							
							$arr_value_2 = array ();
							foreach ( $arr_headermapping [1] as $index => $value_1 ) { // loop
							                                                           // over
							                                                           // subject
								if ($value_1 != "") {
									// $arr_value_2
									$columnname_2 = $arr_headermapping [2] [$index];
									
									if (isset ( $arr_row_data [$index] )) {
										$arr_value_2 [$columnname_2] = $arr_row_data [$index];
									}
									
									if ((isset ( $arr_headermapping [1] [$index + 1] ) && $arr_headermapping [1] [$index + 1] != $value_1) || ! isset ( $arr_headermapping [1] [$index + 1] )) {
	
										$obj_data_tmp = clone $obj_data;
										$obj_data_tmp->$columnname_1 = $value_1;
										foreach ( $arr_value_2 as $key_2 => $value_2 ) {
											if (is_integer ( $key_2 )) {
												$key_2 = "data_$key_2"; // otherwise integer
													                        // in xml tag
											}
											if(is_null($value_2)|| $value_2 == ""){
												$value_2 = 0;
											}
											$value_2 = str_replace(".", ",",$value_2);
											
											$obj_data_tmp->$key_2 = $value_2;
										}
										$arr_value_2 = array ();
										
										$arr_collection [] = $obj_data_tmp;
									}
								}
							}
						}
					} else {
						// $columnname = $arr_dimension_keys[1]['name_key'];
						foreach ( $arr_headermapping [1] as $index => $value_1 ) { // loop
						                                                           // over
						                                                           // year
							if ($value_1 != "") {
								$obj_data->$value_1 = $arr_row_data [$index];
							}
						}
						$arr_collection [] = $obj_data;
					}
					
					break;
			}
			
			$int_counter_row ++;
		}
		return $arr_collection;
	}
}

?>
