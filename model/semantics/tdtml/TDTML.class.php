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
class TDTML {

    public static function TDTPACKAGE() {
        return new Resource(RDFConstants::$TDML_NS . 'TDTPackage');
    }

    public static function TDTRESOURCE() {
        return new Resource(RDFConstants::$TDML_NS . 'TDTResource');
    }

    public static function TDTPROPERTY() {
        return new Resource(RDFConstants::$TDML_NS . 'TDTProperty');
    }
    
    public static function MAPS() {
        return new Resource(RDFConstants::$TDML_NS . 'maps');
    }

    public static function NAME() {
        return new Resource(RDFConstants::$TDML_NS . 'name');
    }
    
    public static function HAS_PROPERTY() {
        return new Resource(RDFConstants::$TDML_NS . 'has_property');
    }
    
    public static function PREFERRED_PROPERTY() {
        return new Resource(RDFConstants::$TDML_NS . 'preferredProperty');
    }
    
    public static function PREFERRED_CLASS() {
        return new Resource(RDFConstants::$TDML_NS . 'preferredClass');
    }
    

    

}

?>
