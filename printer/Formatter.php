<?php

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
        $hash = get_object_vars($this->objectToPrint);
        $hash['version'] = $this->version;
        $hash['timestamp'] = 0;

        $xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><foo />");
        foreach ($hash as $k=>$v) {
            $xml->addChild($k, $v);
        }
        echo $xml;
    }

    static function format_kml($object, $version) {
        /*echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";*/

    }

    static function format_fail($object, $version) {
        return 'epic fail';
    }
}
