<?php

/**
 * This file contains all evaluators for binary functions
 * 
 * @package The-Datatank/universalfilter/interpreter/executers
 * @copyright (C) 2012 We Open Data
 * @license AGPLv3
 * @author Jeroen Penninck
 */

/* equality */
class BinaryFunctionEqualityExecuter extends BinaryFunctionExecuter {
    
    public function getName($nameA, $nameB){
        return $nameA."_isequal_".$nameB;
    }
    
    public function doBinaryFunction($valueA, $valueB){
        return ($valueA==$valueB?"true":"false");
    }
}

/* < */
class BinaryFunctionSmallerExecuter extends BinaryFunctionExecuter {
    
    public function getName($nameA, $nameB){
        return $nameA."_issmaller_".$nameB;
    }
    
    public function doBinaryFunction($valueA, $valueB){
        return ($valueA<$valueB?"true":"false");
    }
}

/* > */
class BinaryFunctionLargerExecuter extends BinaryFunctionExecuter {
    
    public function getName($nameA, $nameB){
        return $nameA."_islarger_".$nameB;
    }
    
    public function doBinaryFunction($valueA, $valueB){
        return ($valueA>$valueB?"true":"false");
    }
}

/* <= */
class BinaryFunctionSmallerEqualExecuter extends BinaryFunctionExecuter {
    
    public function getName($nameA, $nameB){
        return $nameA."_issmallerorequal_".$nameB;
    }
    
    public function doBinaryFunction($valueA, $valueB){
        return ($valueA<=$valueB?"true":"false");
    }
}

/* >= */
class BinaryFunctionLargerEqualExecuter extends BinaryFunctionExecuter {
    
    public function getName($nameA, $nameB){
        return $nameA."_islargerorequal_".$nameB;
    }
    
    public function doBinaryFunction($valueA, $valueB){
        return ($valueA>=$valueB?"true":"false");
    }
}

/* != */
class BinaryFunctionNotEqualExecuter extends BinaryFunctionExecuter {
    
    public function getName($nameA, $nameB){
        return $nameA."_isnotequal_".$nameB;
    }
    
    public function doBinaryFunction($valueA, $valueB){
        return ($valueA!=$valueB?"true":"false");
    }
}

/* or */
class BinaryFunctionOrExecuter extends BinaryFunctionExecuter {
    
    public function getName($nameA, $nameB){
        return $nameA."_or_".$nameB;
    }
    
    public function doBinaryFunction($valueA, $valueB){
        return (($valueA=="true") || ($valueB=="true")?"true":"false");
    }
}

/* and */
class BinaryFunctionAndExecuter extends BinaryFunctionExecuter {
    
    public function getName($nameA, $nameB){
        return $nameA."_and_".$nameB;
    }
    
    public function doBinaryFunction($valueA, $valueB){
        return (($valueA=="true") && ($valueB=="true")?"true":"false");
    }
}

/* plus */
class BinaryFunctionPlusExecuter extends BinaryFunctionExecuter {
    
    public function getName($nameA, $nameB){
        return $nameA."_plus_".$nameB;
    }
    
    public function doBinaryFunction($valueA, $valueB){
        return "".($valueA+$valueB);
    }
}

/* minus */
class BinaryFunctionMinusExecuter extends BinaryFunctionExecuter {
    
    public function getName($nameA, $nameB){
        return $nameA."_minus_".$nameB;
    }
    
    public function doBinaryFunction($valueA, $valueB){
        return "".($valueA-$valueB);
    }
}

/* multiply */
class BinaryFunctionMultiplyExecuter extends BinaryFunctionExecuter {
    
    public function getName($nameA, $nameB){
        return $nameA."_multiply_".$nameB;
    }
    
    public function doBinaryFunction($valueA, $valueB){
        return "".($valueA*$valueB);
    }
}

/* divide */
class BinaryFunctionDivideExecuter extends BinaryFunctionExecuter {
    
    public function getName($nameA, $nameB){
        return $nameA."_isnotequal_".$nameB;
    }
    
    public function doBinaryFunction($valueA, $valueB){
        return "".($valueA/$valueB);
    }
}

/* match regex */
class BinaryFunctionMatchRegexExecuter extends BinaryFunctionExecuter {
    
    public function getName($nameA, $nameB){
        return $nameA."_matches_".$nameB;
    }
    
    public function doBinaryFunction($valueA, $valueB){
        //TODO Regular expressions
        throw new Exception("TODO Regular expressions are not supported yet....");
    }
}
?>
