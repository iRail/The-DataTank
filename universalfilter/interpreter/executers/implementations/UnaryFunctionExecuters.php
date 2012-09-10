<?php

/**
 * This file contains all evaluators for unary functions
 * 
 * @package The-Datatank/universalfilter/interpreter/executers
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jeroen Penninck
 */

/* upercase */
class UnaryFunctionUppercaseExecuter extends UnaryFunctionExecuter {
    
    public function getName($name){
        return "uppercase_".$name;
    }
    
    public function doUnaryFunction($value){
        if($value===null) return null;
        return strtoupper($value);
    }
}

/* lowercase */
class UnaryFunctionLowercaseExecuter extends UnaryFunctionExecuter {
    
    public function getName($name){
        return "lowercase_".$name;
    }
    
    public function doUnaryFunction($value){
        if($value===null) return null;
        return strtolower($value);
    }
}

/* stringlength */
class UnaryFunctionStringLengthExecuter extends UnaryFunctionExecuter {
    
    public function getName($name){
        return "length_".$name;
    }
    
    public function doUnaryFunction($value){
        if($value===null) return null;
        return strlen($value);
    }
}

/* round */
class UnaryFunctionRoundExecuter extends UnaryFunctionExecuter {
    
    public function getName($name){
        return "round_".$name;
    }
    
    public function doUnaryFunction($value){
        if($value===null) return null;
        return round($value);
    }
}

/* isnull */
class UnaryFunctionIsNullExecuter extends UnaryFunctionExecuter {
    
    public function getName($name){
        return "isnull_".$name;
    }
    
    public function doUnaryFunction($value){
        return (is_null($value)?"true":"false");
    }
}

/* not */
class UnaryFunctionNotExecuter extends UnaryFunctionExecuter {
    
    public function getName($name){
        return "not_".$name;
    }
    
    public function doUnaryFunction($value){
        if($value===null) return null;
        return ($value=="true" || $value==1?"false":"true");
    }
}
?>