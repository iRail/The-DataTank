<?php

/**
 * This file contains all evaluators for unary functions
 * 
 * @package The-Datatank/universalfilter/interpreter/executers
 * @copyright (C) 2012 We Open Data
 * @license AGPLv3
 * @author Jeroen Penninck
 */

/* upercase */
class UnaryFunctionUppercaseExecuter extends UnaryFunctionExecuter {
    
    public function getName($name){
        return "uppercase_".$name;
    }
    
    public function doUnaryFunction($value){
        return strtoupper($value);
    }
}

/* lowercase */
class UnaryFunctionLowercaseExecuter extends UnaryFunctionExecuter {
    
    public function getName($name){
        return "lowercase_".$name;
    }
    
    public function doUnaryFunction($value){
        return strtolower($value);
    }
}

/* stringlength */
class UnaryFunctionStringLengthExecuter extends UnaryFunctionExecuter {
    
    public function getName($name){
        return "length_".$name;
    }
    
    public function doUnaryFunction($value){
        return strlen($value);
    }
}

/* round */
class UnaryFunctionRoundExecuter extends UnaryFunctionExecuter {
    
    public function getName($name){
        return "round_".$name;
    }
    
    public function doUnaryFunction($value){
        return round($value);
    }
}

/* isnull */
class UnaryFunctionIsNullExecuter extends UnaryFunctionExecuter {
    
    public function getName($name){
        return "isnull_".$name;
    }
    
    public function doUnaryFunction($value){
        return is_null($value);
    }
}
?>