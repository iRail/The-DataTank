<?php

class Serializer {
    public static function serialize($obj, $format='json') {
        switch ($format) {
            case 'json':
                if(is_object($obj)){
                    $obj = get_object_vars($obj);
                }
                return json_encode($obj);
                break;
            default:
                throw Exception('Format not supported');
        }
    }
}

?>
