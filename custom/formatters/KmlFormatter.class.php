<?php
/**
 * The Kml-formatter is a formatter which will search for location objects throughout the documenttree and return a file with placemarks 
 *
 * @package The-Datatank/formatters
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@iRail.be>
 * @author Pieter Colpaert   <pieter@iRail.be>
 */

/**
 * This class inherits from the abstract Formatter.
 * It will return our resultobject into a kml
 * datastructure.
 */
class KmlFormatter extends AFormatter {
	public function __construct($rootname, $objectToPrint) {
		parent::__construct ( $rootname, $objectToPrint );
	}
	public function printHeader() {
		 header ( "Access-Control-Allow-Origin: *" );
		 header ( "Content-Type: application/vnd.google-earth.kml+xml;charset=utf-8" );
	}
	public function printBody() {
		/*
		 * print the KML header first
		 */
		echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>";
		echo "<kml xmlns=\"http://www.opengis.net/kml/2.2\">";
		/*
		 * Second step is to check every locatable object and print it
		 */
		echo "<Document>";
		
		$this->printPlacemarks ( $this->objectToPrint );
		echo "</Document>";
		
		echo "</kml>";
	}
	
	/**
	 * The first parameter is the name of an object.
	 * The second is an !object!
	 */
	private function printPlacemarks($val) {
		$hash = get_object_vars ( $val );
		$this->printArray ( $hash );
	}
	private function xmlgetelement($value) {
		$result = "<![CDATA[";
		if (is_object ( $value )) {
			$array = get_object_vars ( $value );
			foreach ( $array as $key => $val ) {
				if (is_numeric ( $key )) {
					$key = "int_" . $key;
				}
				$result .= "<" . $key . ">" . $val . "</" . $key . ">";
			}
		} else if (is_array ( $value )) {
			foreach ( $value as $key => $val ) {
				if (is_numeric ( $key )) {
					$key = "int_" . $key;
				}
				$result .= "<" . $key . ">" . $val . "</" . $key . ">";
			}
		} else {
			$result .= $value;
		}
		$result .= "]]>";
		return $result;
	}
	private function getExtendedDataElement($value) {
		$result = "<ExtendedData>";
		if (is_object ( $value )) {
			$array = get_object_vars ( $value );
			foreach ( $array as $key => $val ) {
				if (is_numeric ( $key )) {
					$key = "int_" . $key;
				}
				$key = htmlspecialchars ( str_replace ( " ", "", $key ) );
				$val = htmlspecialchars ( $val );
				$result .= '<Data name="' . $key . '"><value>' . $val . '</value></Data>';
			}
		} else if (is_array ( $value )) {
			foreach ( $value as $key => $val ) {
				if (is_numeric ( $key )) {
					$key = "int_" . $key;
				}
				$key = htmlspecialchars ( str_replace ( " ", "", $key ) );
				$val = htmlspecialchars ( $val );
				$result .= '<Data name="' . $key . '"><value>' . $val . '</value></Data>';
			}
		} else {
			$result .= htmlspecialchars ( $value );
		}
		$result .= "</ExtendedData>";
		return $result;
	}
	private function printArray(&$val) {
		// var_dump($val);
		foreach ( $val as $key => &$value ) {
			$long = "";
			$lat = "";
			$coords = array ();
			if (is_array ( $value )) {
				$array = $value;
			}
			if (is_object ( $value )) {
				$array = get_object_vars ( $value );
			}
			if (isset ( $array )) {
				$arr_longkeys = array (
						"long",
						"longitude",
						"lng",
						"point_lng",
						"point_long",
						"gisx" 
				);
				foreach ( $arr_longkeys as $longkey_check ) {
					$longkey = $this->array_key_exists_nc ( $longkey_check, $array );
					if ($longkey != "") {
						break;
					}
				}
				
				$arr_latkeys = array (
						"lat",
						"latitude",
						"point_lat",
						"gisy" 
				);
				foreach ( $arr_latkeys as $latkey_check ) {
					$latkey = $this->array_key_exists_nc ( $latkey_check, $array );
					if ($latkey != "") {
						break;
					}
				}
				
				$arr_coordskeys = array (
						"coords",
						"coordinates",
						"polygone",
						"polygon",
						"geometry" 
				);

				foreach ( $arr_coordskeys as $coordkey_check ) {
					$coordskey = $this->array_key_exists_nc ( $coordkey_check, $array );
					if ($coordskey != "") {
						break;
					}
				}
				
				if ($longkey && $latkey) {
					$long = $array [$longkey];
					$lat = $array [$latkey];
					unset ( $array [$longkey] );
					unset ( $array [$latkey] );
					$name = $this->xmlgetelement ( $array );
					$extendeddata = $this->getExtendedDataElement ( $array );
				} else if ($coordskey) {
					$obj_geometry = new stdClass ();
					
					$arr_coordinates = array ();
					switch (true) {
						case strpos ( $array [$coordskey], "[" ) !== false :
							// geojson
							$coords_decoded = json_decode ( $array [$coordskey] );
							if (isset ( $coords_decoded->type )) {
								// geometry object
								$obj_geometry = $coords_decoded;
							} else {
								// geometry.coordinates array
								if (isset ( $coords_decoded [0] [0] ) && is_array ( $coords_decoded [0] [0] )) {
									// check if first element is the same as the
									// last
									if ($coords_decoded [0] [0] == $coords_decoded [0] [count ( $coords_decoded [0] ) - 1]) {
										// check if there is > 1 root level(case
										// of holes or multipolygon)
										if (isset ( $coords_decoded [0] [1] )) {
											if (isset ( $coords_decoded [0] [0] [0] [0] )) {
												$type = "MultiPolygon";
											} else {
												$type = "Polygon"; // multipolygon
											}
										} else {
											$type = "Polygon";
										}
									} else {
										$type = "MultiLineString";
									}
								} else {
									if (is_array ( $coords_decoded [0] )) {
										$type = "LineString"; // or MultiPoint
									} else {
										$type = "Point";
									}
								}
								$obj_geometry->type = $type;
								$obj_geometry->coordinates = $coords_decoded;
							}
							break;
						default :
							$arr_polygons = array ();
							if (strpos ( $array [$coordskey], "|" ) !== false) {
								$obj_geometry->type = "MultiPolygon";
								$arr_polygons = explode ( "|", $array [$coordskey] );
							} else {
								$obj_geometry->type = "Polygon";
								$arr_polygons [] = $array [$coordskey];
							}
							$counter_polygon = 0;
							foreach ( $arr_polygons as $polygon ) {
								$arr_coords_strings = explode ( " ", $polygon );
								$arr_coords_strings = array_filter ( $arr_coords_strings );
								foreach ( $arr_coords_strings as $arr_coords_string ) {
									if (strpos ( $array [$coordskey], ":" ) !== false) {
										$arr_coords = explode ( ":", $arr_coords_string );
									} else {
										$arr_coords = explode ( ",", $arr_coords_string );
									}
									$arr_latlong = array ();
									$arr_latlong [0] = $arr_coords [0];
									$arr_latlong [1] = $arr_coords [1];
									
									switch ($obj_geometry->type) {
										case "Polygon" :
											$arr_coordinates [$counter_polygon] [] = $arr_latlong;
											break;
										case "MultiPolygon" :
											$arr_coordinates [$counter_polygon] [0] [] = $arr_latlong;
											break;
									}
								}
								$counter_polygon ++;
							}
							$obj_geometry->coordinates = $arr_coordinates;
							break;
					}
					
					unset ( $array [$coordskey] );
					$name = $this->xmlgetelement ( $array );
					$extendeddata = $this->getExtendedDataElement ( $array );
				} else {
					$this->printArray ( $array );
				}
				
				if (($lat != "" && $long != "") || isset ( $obj_geometry->coordinates[0] )) {
					echo "<Placemark><name>$key</name><Description>" . $name . "</Description>";
					echo $extendeddata;
					if ($lat != "" && $long != "") {
						echo "<Point><coordinates>" . $long . "," . $lat . "</coordinates></Point>";
					}
					
					
					if ($obj_geometry->type == 'Polygon') {
						echo "<Polygon><outerBoundaryIs><LinearRing><coordinates>";
						foreach ( $obj_geometry->coordinates [0] as $arr_latlong ) {
							echo $arr_latlong [0] . "," . $arr_latlong [1] . " ";
						}
						echo "</coordinates></LinearRing></outerBoundaryIs></Polygon>";
					} elseif ($obj_geometry->type == 'MultiPolygon') {
						echo "<MultiGeometry>";
						foreach ( $obj_geometry->coordinates  as $arr_polygon ) {
							echo "<Polygon><outerBoundaryIs><LinearRing><coordinates>";
							foreach ( $arr_polygon[0]  as $arr_latlong ) {
								echo $arr_latlong [0] . "," . $arr_latlong [1] . " ";
							}
							echo "</coordinates></LinearRing></outerBoundaryIs></Polygon>";
						}
						echo "</MultiGeometry>";
					}
					
					echo "</Placemark>";
				}
			}
		}
	}
	
	/**
	 * Case insensitive version of array_key_exists.
	 * Returns the matching key on success, else false.
	 *
	 * @param string $key        	
	 * @param array $search        	
	 * @return string false
	 */
	private function array_key_exists_nc($key, $search) {
		if (array_key_exists ( $key, $search )) {
			return $key;
		}
		if (! (is_string ( $key ) && is_array ( $search ) && count ( $search ))) {
			return false;
		}
		$key = strtolower ( $key );
		foreach ( $search as $k => $v ) {
			if (strtolower ( $k ) == $key) {
				return $k;
			}
		}
		return false;
	}
	public static function getDocumentation() {
		return "Will try to find locations in the entire object and print them as KML points";
	}
}
;
?>
