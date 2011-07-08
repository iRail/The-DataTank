<?php

require_once 'XML/Serializer.php';

class Formatter {
    static function format($rootname, $format, $object, $version, $callback=null) {
        if ($format == "Json") {
            return Formatter::format_json($rootname, $object, $version);
        } else if ($format == "Jsonp") {
            return Formatter::format_jsonp($rootname, $object, $version, $callback);
        } else if ($format == "Xml") {
            return Formatter::format_xml($rootname, $object, $version);
        } else if ($format == "Kml") {
            return Formatter::format_kml($rootname, $object, $version);
        } else if ($format == "Php") {
            return Formatter::format_php($rootname, $object, $version);
        } else {
	        throw new NoPrinterTDTException();
	    }
    }

    static function format_json($rootname, $object, $version) {
        $hash = get_object_vars($object);
        $hash['version'] = $version;
        $hash['timestamp'] = 0;
        return json_encode($hash);
    }
    
    static function format_jsonp($rootname, $object, $version, $callback) {
        return $callback . '(' .
            Formatter::format_json($rootname, $object, $version) . ')';
    }

    static function format_xml($rootname, $object, $version) {
        $hash = get_object_vars($object);
        $hash['version'] = $version;
        $hash['timestamp'] = 0;
        #TODO find a way to solve the whole double indexing 'problem'
        $options = array (
            'addDecl' => TRUE,
            'encoding' => 'utf-8',
            'indent' => '  ',
            'rootName' => $rootname,
            'defaultTagName' => 'item',
        ); 
        $serializer = new XML_Serializer($options);
        $status = $serializer->serialize($hash);
   }

    static function format_php($rootname, $object, $version) {
        return serialize($object);
        //TODO check if not truly the biggest sercurity hole ever!
    }

    static function getDoc($method) {
        if ($method == "format_json") {
            return 'serialize $object into json and return the result.';
        } else if ($method == "format_jsonp") {
            return 'serialize $object into json and call $callback with the encoded' .
                'json and return that result.\n' .
                'ex. callback("{\'foo\': \'bar\'}")';
        } else if ($method == "format_xml") {
            return 'serialize $object into xml and return the result.';
        } else if ($method == "format_kml") {
            return 'serialize $object into kml and return the result. ' .
                'When an attribute\'s type coresponds with a location type as defined ' .
                'by the #TODO add url specifications the retuned xml will use the ' .
                'proper content type.';
        } else if ($method == "format_php") {
            return 'This functions returns the $object serialized by the php serialize ' .
                'function as to simplify the integration with oter php code.';
        }
    }
}
?>
