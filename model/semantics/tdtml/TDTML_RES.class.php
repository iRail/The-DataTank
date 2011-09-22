<?php

/**
 *   
 *
 *   @version 1.0
 *   @author Miel Vander Sande
 *   @package vocabulary
 *
 *   Wrapper, defining resources for all terms of the
 *   TDML.
 *   For details about TDML see: .
 *   Using the wrapper allows you to define all aspects of
 *   the vocabulary in one spot, simplifing implementation and
 *   maintainence.
 */
class TDML_RES {

    public static function TDTPACKAGE() {
        return new ResResource(RDFConstants::$TDML_NS . 'TDTPackage');
    }

    public static function TDTRESOURCE() {
        return new ResResource(RDFConstants::$TDML_NS . 'TDTResource');
    }

    public static function TDTPROPERTY() {
        return new ResResource(RDFConstants::$TDML_NS . 'TDTProperty');
    }
    
    public static function IS_A() {
        return new ResProperty(RDFConstants::$TDML_NS . 'is_a');
    }

    public static function MAPS() {
        return new ResProperty(RDFConstants::$TDML_NS . 'maps');
    }

    public static function NAME() {
        return new ResProperty(RDFConstants::$TDML_NS . 'name');
    }
    
    public static function HAS_RESOURCES() {
        return new ResProperty(RDFConstants::$TDML_NS . 'has_resources');
    }
    

}

?>
