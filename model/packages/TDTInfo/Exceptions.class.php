<?php
/**
 * This is a class which will return all the packages in The DataTank
 * 
 * @package The-Datatank/packages/TDTInfo
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@iRail.be>
 */

class TDTInfoExceptions extends AReader {
    
    public static function getParameters() {
        return array();
    }
    
    public static function getRequiredParameters() {
        return array();
    }
    
    public function setParameter($key, $val) {
    }
    
    public function read(){
       
        $tmp = array();
        
        $classes = get_declared_classes();
        foreach($classes as $class) {
            if(is_subclass_of($class, "AbstractTDTException") && get_parent_class($class) != "AbstractTDTException") {
                $e = new stdClass();
                $e->code = $class::getErrorCode();
                $e->doc = $class::getDoc();
                $e->type = get_parent_class($class);
                array_push($tmp,$e);             
            }
        }
        return $tmp;
    }
    
    public static function getDoc() {
        return "This resource contains every exception used by this DataTank instance.";
    }
    

}

?>
