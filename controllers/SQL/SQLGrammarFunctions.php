<?php
/**
 * This file is used by the grammar to create the tree
 *
 * @package The-Datatank/controllers/SQL
 * @copyright (C) 2012 We Open Data
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
    return $SQLRegex;
}

/**
 * Gets the universal name (and filter) for a unary SQLFunction
 */
function getUnaryFilterForSQLFunction($SQLname, $arg1){
    $unarymap = array(
        "UCASE" => UnairyFunction::$FUNCTION_UNAIRY_UPPERCASE,
        "LCASE" => UnairyFunction::$FUNCTION_UNAIRY_LOWERCASE,
        "LEN" => UnairyFunction::$FUNCTION_UNAIRY_STRINGLENGTH,
        "ROUND" => UnairyFunction::$FUNCTION_UNAIRY_ROUND,
        "ISNULL" => UnairyFunction::$FUNCTION_UNAIRY_ISNULL
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
    throw new Exception("That binary function does not exist... (".$SQLname.")");
}

/**
 * Gets the universal name (and filter) for a tertary SQLFunction
 */
function getTertairyFunctionForSQLFunction($SQLname, $arg1, $arg2){
    $tertarymap = array(
        "MID" => TertairyFunction::$FUNCTION_TERTIARY_SUBSTRING
    );
    
    if($tertarymap[$SQLname]!=null){
        return new AggregatorFunction($tertarymap[$SQLname], $arg1);
    }else{
        throw new Exception("That tertary function does not exist... (".$SQLname.")");
    }
}