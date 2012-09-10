<?php

/**
 * This file contains all evaluators for tertairy functions
 * 
 * @package The-Datatank/universalfilter/interpreter/executers
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jeroen Penninck
 */

/* substring / MID */
class TertairyFunctionSubstringExecuter extends TertairyFunctionExecuter {
    
    public function getName($nameA, $nameB, $nameC){
        return "substring_".$nameA."_".$nameB."_".$nameC;
    }
    
    public function doTertairyFunction($valueA, $valueB, $valueC){
        if($valueA===null || $valueB===null || $valueC===null) return null;
        return substr($valueA, $valueB, $valueC);
    }
}

/* regex replace */
class TertairyFunctionRegexReplacementExecuter extends TertairyFunctionExecuter {
    
    public function getName($nameA, $nameB, $nameC){
        return $nameA."_replaced_".$nameB."_with_".$nameC;
    }
    
    public function doTertairyFunction($valueA, $valueB, $valueC){
        if($valueA===null || $valueB===null || $valueC===null) return null;
        return preg_replace($valueA, $valueB, $valueC);
    }
}

?>
