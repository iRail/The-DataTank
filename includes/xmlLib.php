<?php

/*
	Original source from: http://codes.myfreewares.com/php/XML/XML%20@=@%20Associative%20Array.php-file.html
	Modified by Chris Jean (http://gaarai.com/)
	
	Version History
		1.0 - 2009-01-12
			Original modification
		1.1 - 2009-02-11
			Added var testing to remove warnings from ArrayToXML code
			Fixed incorrect parent nesting in ArrayToXML
			Added xmlLibTest.php file to archive
	
	Examples
		XMLToArray -- Convert XML to Associative Array
			Usage:		$xml = new XMLToArray( xmldata (string), ignorefields (array 1,2,3), replacefields(array OLD => NEW), show attributes?, convert to upper );
						$array = $xml->getArray();
			Example:	$xml = new XMLToArray( 'http://www.slashdot.org/slashdot.xml', array( 'backslash' ), array( 'story' => '_array_' ), true, false );
						print_r( $xml->getArray() );
						print_r( $xml->getReplaced() );
						print_r( $xml->getAttributes() );
		
		ArrayToXML -- Convert Associative Array to XML
			Usage:		$array = new ArrayToXML( $xml->getArray(), $xml->getReplaced(), $xml->getAttributes() )
						$xml = $array->getXML();
*/

class ArrayToXML {
	var $_data;
	var $_name = Array();
	var $_rep  = Array();
	var $_parser = 0;
	var $_ignore, $_err, $_errline, $_replace, $_attribs, $_parent;
	var $_level = 0;
	
	function ArrayToXML( &$data, $replace = Array(), $attribs = Array() ) {
		$this->_attribs = $attribs;
		$this->_replace = $replace;
		$this->_data = $this->_processArray( $data );
	}
	
	function & getXML() {
		return $this->_data;
	}
	
	function _processArray( &$array, $level = 0, $parent = '' ) {
		//ksort($array);
		$return = '';
		foreach ( (array) $array as $name => $value ) {
			$tlevel = $level;
			$isarray = false;
			$attrs = '';
			
			if ( is_array( $value ) && ( sizeof( $value ) > 0 ) && array_key_exists( 0, $value ) ) {
				$tlevel = $level - 1;
				$isarray = true;
			}
			elseif ( ! is_int( $name ) ) {
				if ( ! isset( $this->_rep[$name] ) )
					$this->_rep[$name] = 0;
				$this->_rep[$name]++;
			}
			else {
				$name = $parent;
				if ( ! isset( $this->_rep[$name] ) )
					$this->_rep[$name] = 0;
				$this->_rep[$name]++;
			}
			
			if ( ! isset( $this->_rep[$name] ) )
				$this->_rep[$name] = 0;
			
			if ( isset( $this->_attribs[$tlevel][$name][$this->_rep[$name] - 1] ) && is_array( $this->_attribs[$tlevel][$name][$this->_rep[$name] - 1] ) ) {
				foreach ( (array) $this->_attribs[$tlevel][$name][$this->_rep[$name] - 1] as $aname => $avalue ) {
					unset( $value[$aname] );
					$attrs .= " $aname=\"$avalue\"";
				}
			}
			if ( $this->_replace[$name] )
				$name = $this->_replace[$name];
			
			is_array( $value ) ? $output = $this->_processArray( $value, $tlevel + 1, $name ) : $output = htmlspecialchars( $value );
			
			$isarray ? $return .= $output : $return .= "<$name$attrs>$output</$name>\n";
		}
		return $return;
	}
}

class XMLToArray {
	var $_data = Array();
	var $_name = Array();
	var $_rep  = Array();
	var $_parser = 0;
	var $_ignore = Array(), $_replace = Array(), $_showAttribs;
	var $_level = 0;
	
	function XMLToArray( $data, $ignore = Array(), $replace = Array(), $showattribs = false, $toupper = false) {
		if ( preg_match( '@^(https?|ftp)://@', $data ) ) {
			if ( $stream = fopen( $data, 'r' ) ) {
				$data = stream_get_contents( $stream );
				fclose( $stream );
			}
			else
				return false;
		}
		if ( file_exists( $data ) )
			$data = file_get_contents( $data );
		
		$this->_showAttribs = $showattribs;
		$this->_parser  = xml_parser_create();
		
		xml_set_object( $this->_parser, $this );
		if ( $toupper ) {
			foreach ( (array) $ignore as $key => $value )
				$this->_ignore[strtoupper( $key )]  = strtoupper( $value );
			foreach ( (array) $replace as $key => $value)
				$this->_replace[strtoupper( $key )] = strtoupper( $value );
			xml_parser_set_option( $this->_parser, XML_OPTION_CASE_FOLDING, true);
		}
		else {
			$this->_ignore  = &$ignore;
			$this->_replace = &$replace;
			xml_parser_set_option( $this->_parser, XML_OPTION_CASE_FOLDING, false);
		}
		xml_set_element_handler( $this->_parser, '_startElement', '_endElement' );
		xml_set_character_data_handler( $this->_parser, '_cdata');
		
		$this->_data = array();
		$this->_level = 0;
		if( ! xml_parse( $this->_parser, $data, true ) ) {
			//new Error("XML Parse Error: ".xml_error_string(xml_get_error_code($this->_parser))."n on line: ".xml_get_current_line_number($this->_parser),true);
			return false;
		}
		xml_parser_free( $this->_parser );
	}
	
	function & getArray() {
		return $this->_data[0];
	}
	
	function & getReplaced() {
		return $this->_data['_Replaced_'];
	}
	
	function & getAttributes() {
		return $this->_data['_Attributes_'];
	}
	
	function _startElement( $parser, $name, $attrs ) {
		if ( ! isset( $this->_rep[$name] ) )
			$this->_rep[$name] = 0;
		if ( ! in_array( $name, $this->_ignore ) ) {
			$this->_addElement( $name, $this->_data[$this->_level], $attrs, true );
			$this->_name[$this->_level] = $name;
			$this->_level++;
		}
	}
	
	function _endElement( $parser, $name ) {
		if ( ! in_array( $name, $this->_ignore ) && isset( $this->_name[$this->_level - 1] ) ) {
			if ( isset( $this->_data[$this->_level] ) )
				$this->_addElement( $this->_name[$this->_level - 1], $this->_data[$this->_level - 1], $this->_data[$this->_level], false );
			
			unset( $this->_data[$this->_level] );
			$this->_level--;
			$this->_rep[$name]++;
		}
	}
	
	function _cdata( $parser, $data ) {
		if ( ! empty( $this->_name[$this->_level - 1] ) )
			$this->_addElement( $this->_name[$this->_level - 1], $this->_data[$this->_level - 1], str_replace( array( '&gt;', '&lt;', '&quot;', '&amp;' ), array( '>', '<', '"', '&' ), $data ), false );
	}
	
	function _addElement( &$name, &$start, $add = array(), $isattribs = false ) {
		if ( ( ( sizeof( $add ) == 0 ) && is_array( $add ) ) || ! $add ) {
			if ( ! isset( $start[$name] ) )
				$start[$name] = '';
			$add = '';
			//if (is_array($add)) return;
				//return;
		}
		if ( ! empty( $this->_replace[$name] ) && ( '_ARRAY_' === strtoupper( $this->_replace[$name] ) ) ) {
			if ( ! $start[$name] )
				$this->_rep[$name] = 0;
			$update = &$start[$name][$this->_rep[$name]];
		}
		elseif ( ! empty( $this->_replace[$name] ) ) {
			if ( $add[$this->_replace[$name]] ) {
				$this->_data['_Replaced_'][$add[$this->_replace[$name]]] = $name;
				$name = $add[$this->_replace[$name]];
			}
			$update = &$start[$name];
		}
		else
			$update = &$start[$name];
		
		if ( $isattribs && ! $this->_showAttribs )
			return;
		elseif ( $isattribs )
			$this->_data['_Attributes_'][$this->_level][$name][] = $add;
		elseif ( is_array( $add ) && is_array( $update ) )
			$update += $add;
		elseif ( is_array( $update ) )
			return;
		elseif ( is_array( $add ) )
			$update = $add;
		elseif ( $add )
			$update .= $add;
	}
}


?>
