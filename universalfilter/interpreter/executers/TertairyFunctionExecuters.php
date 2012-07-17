<?php

/**
 * This file contains all evaluators for tertairy functions
 * 
 * @package The-Datatank/universalfilter/interpreter/executers
 * @copyright (C) 2012 We Open Data
 * @license AGPLv3
 * @author Jeroen Penninck
 */

/* substring / MID */
class TertairyFunctionSubstringExecuter extends TertairyFunctionExecuter {
    
    public function getName($nameA, $nameB, $nameC){
        return "substring_".$nameA."_".$nameB."_".$nameC;
    }
    
    public function doTertairyFunction($valueA, $valueB, $valueC){
        return substr($valueA, $valueB, $valueC);
    }
}

?>
