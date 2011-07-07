<?php

require_once 'XML/Serializer.php';

class Formatter {
    static function format($format, $object, $version) {
        if ($format == "Json") {
            return Formatter::format_json($object, $version);
        } else if ($format == "Jsonp") {
            return Formatter::format_jsonp($object, $version);
        } else if ($format == "Xml") {
            return Formatter::format_xml($object, $version);
        } else if ($format == "Kml") {
            return Formatter::format_kml($object, $version);
        } else if ($format == "Php") {
             return Formatter::format_php($object, $version);
        } else {
            return Formatter::format_fail($object, $version);
        }
    }

    static function format_json($object, $version) {
        $hash = get_object_vars($object);
        $hash['version'] = $version;
        $hash['timestamp'] = 0;
        return  json_encode($hash);
    }
    
    static function format_jsonp($object, $version) {
        //
    }

    static function format_xml($object, $version) {
        $hash = get_object_vars($object);
        $hash['version'] = $version;
        $hash['timestamp'] = 0;

        $options = array (
            'addDecl' => TRUE,
            'encoding' => 'utf-8',
            'indent' => '  ',
            'rootName' => 'data',
        ); 
        $serializer = new XML_Serializer($options);
        $status = $serializer->serialize($hash);
        return $serializer->getSerializedData();
    }

    static function format_kml($object, $version) {
        /*echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";*/

    }

    static function format_php($object, $version) {
        return var_dump($object);
    }

    static function format_fail($object, $version) {
        return 'epic fail';
    }
}
