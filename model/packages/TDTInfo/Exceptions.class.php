<?php
/**
 * This is a class which will return all the packages in The DataTank
 * 
 * @package The-Datatank/packages/TDTInfo
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@iRail.be>
 */

class Exceptions extends AResource {
    
    public static function getParameters() {
        return array();
    }
    
    public static function getRequiredParameters() {
        return array();
    }
    
    public function setParameter($key, $val) {
    }
    
    public function call() {
        $o = new stdClass();
        $o->Exceptions = array();
        
        $classes = get_declared_classes();
        foreach($classes as $class) {
            if(is_subclass_of($class, "AbstractTDTException") && get_parent_class($class) != "AbstractTDTException") {
                $e = new stdClass();
                $e->code = $class::getErrorCode();
                $e->doc = $class::getDoc();
                $e->type = get_parent_class($class);
                $o->Exceptions[$class] = $e;
            }
        }
        return $o;
    }
    
    public static function getAllowedFormatters() {
        return array("json", "xml", "jsonp", "php", "html");
    }
    
    public static function getDoc() {
        return "This resource contains every exception used by this DataTank instance.";
    }
}

?>
