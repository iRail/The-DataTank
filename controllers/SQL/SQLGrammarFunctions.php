<?php
/**
 * This file is used by the grammar to create the tree
 *
 * @package The-Datatank/controllers/SQL
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jeroen Penninck
 */

include_once("universalfilter/UniversalFilters.php");

/**
 * This function appends a filter to the list of filters
 * (But only if the filter to append is not null)
 */
function putFilterAfterIfExists($filter, $filterToPutAfter){
    if($filterToPutAfter!=null){
        if($filterToPutAfter->getSource()==null){
            $filterToPutAfter->setSource($filter);
        }else{
            putFilterAfterIfExists($filter, $filterToPutAfter->getSource());
        }
        return $filterToPutAfter;
    }else{
        return $filter;
    }
}

/**
 * Converts the regex from the normal format to the format used in Universal
 */
function convertRegexFromSQLToUniversal($SQLRegex){
    $phpregex = preg_quote($SQLRegex, "/");
    $phpregex = str_replace("%", ".*", $phpregex);
    $phpregex = str_replace("?", ".", $phpregex);
    $phpregex = "/".$phpregex."/";
    return $phpregex;
}

/**
 * Gets the universal name (and filter) for a unary SQLFunction
 */
function getUnaryFilterForSQLFunction($SQLname, $arg1){
    $SQLname=strtoupper($SQLname);
    
    $unarymap = array(
        "UCASE" => UnairyFunction::$FUNCTION_UNAIRY_UPPERCASE,
        "UPPER" => UnairyFunction::$FUNCTION_UNAIRY_UPPERCASE,
        "LCASE" => UnairyFunction::$FUNCTION_UNAIRY_LOWERCASE,
        "LOWER" => UnairyFunction::$FUNCTION_UNAIRY_LOWERCASE,
        "LEN" => UnairyFunction::$FUNCTION_UNAIRY_STRINGLENGTH,
        "ROUND" => UnairyFunction::$FUNCTION_UNAIRY_ROUND,
        "ISNULL" => UnairyFunction::$FUNCTION_UNAIRY_ISNULL,
        "NOT" => UnairyFunction::$FUNCTION_UNAIRY_NOT
    );
    $unaryaggregatormap = array(
        "AVG" => AggregatorFunction::$AGGREGATOR_AVG,
        "COUNT" => AggregatorFunction::$AGGREGATOR_COUNT,
        "FIRST" => AggregatorFunction::$AGGREGATOR_FIRST,
        "LAST" => AggregatorFunction::$AGGREGATOR_LAST,
        "MAX" => AggregatorFunction::$AGGREGATOR_MAX,
        "MIN" => AggregatorFunction::$AGGREGATOR_MIN,
        "SUM" => AggregatorFunction::$AGGREGATOR_SUM
    );
    
    if(isset($unarymap[$SQLname])){
        return new UnairyFunction($unarymap[$SQLname], $arg1);
    }else{
        if($unaryaggregatormap[$SQLname]!=null){
            return new AggregatorFunction($unaryaggregatormap[$SQLname], $arg1);
        }else{
            throw new Exception("That unary function does not exist... (".$SQLname.")");
        }
    }
    
}

/**
 * Gets the universal name (and filter) for a binary SQLFunction
 */
function getBinaryFunctionForSQLFunction($SQLname, $arg1, $arg2){
    //all binary functions like "+", "*", ... are defined in the grammar
    $SQLname=strtoupper($SQLname);
    
    $binarymap = array(
        "REGEX_MATCH" => BinaryFunction::$FUNCTION_BINARY_MATCH_REGEX
    );
    
    if($binarymap[$SQLname]!=null){
        return new BinaryFunction($binarymap[$SQLname], $arg1);
    }else{
        throw new Exception("That tertary function does not exist... (".$SQLname.")");
    }
}

/**
 * Gets the universal name (and filter) for a tertary SQLFunction
 */
function getTertairyFunctionForSQLFunction($SQLname, $arg1, $arg2,$arg3){
    $SQLname=strtoupper($SQLname);
    
    $tertarymap = array(
        "MID" => TertairyFunction::$FUNCTION_TERTIARY_SUBSTRING,
		  "SUBSTRING" => TertairyFunction::$FUNCTION_TERTIARY_SUBSTRING,
        "REGEX_REPLACE" => TertairyFunction::$FUNCTION_TERTIARY_REGEX_REPLACE
    );
    
    if($tertarymap[$SQLname]!=null){
        return new TertairyFunction($tertarymap[$SQLname], $arg1,$arg2,$arg3);
    }else{
        throw new Exception("That tertary function does not exist... (".$SQLname.")");
    }
}
