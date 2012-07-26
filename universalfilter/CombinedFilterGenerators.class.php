<?php

/**
 * This file contains some methods to create some useful combinations of the nodes in the UniversalFilterTree
 *
 * @package The-Datatank/universalfilter
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jeroen Penninck
 */
class CombinedFilterGenerators {
    
    /**
     * This function sets the source of a combined filer
     * @param NormalFilterNode $for
     * @param UniversalFilterNode $sourceToSet 
     */
    public static function setCombinedFilterSource(NormalFilterNode $for, UniversalFilterNode $sourceToSet){
        if($for->getSource()==null){
            $for->setSource($filter);
        }else{
            CombinedFilterGenerators::setCombinedFilterSource($for->getSource(), $sourceToSet);
        }
    }
    
    /**
     * Creates a BETWEEN-filter  (inclusive!)
     * 
     * @param UniversalFilterNode $a what to filter
     * @param UniversalFilterNode $b left bound
     * @param UniversalFilterNode $c right bound
     * @return NormalFilterNode the filter
     */
    public static function makeBetweenFilter(UniversalFilterNode $a, UniversalFilterNode $b, UniversalFilterNode $c){
        return new BinaryFunction(BinaryFunction::$FUNCTION_BINARY_AND, 
            new BinaryFunction(BinaryFunction::$FUNCTION_BINARY_COMPARE_SMALLER_OR_EQUAL_THAN, $b, $a), 
            new BinaryFunction(BinaryFunction::$FUNCTION_BINARY_COMPARE_SMALLER_OR_EQUAL_THAN, $a, $c));
    }
    
    /**
     * Creates a smaller (or equal) than ALL/ANY  filter
     * 
     * @param UniversalFilterNode $a the left side
     * @param UniversalFilterNode $b the right side
     * @param boolean $strictSmaller <= or <
     * @param boolean $isAllFilter ALL or ANY ?
     * @return NormalFilterNode the filter
     */
    public static function makeSmallerThanAllOrAnyFilter(UniversalFilterNode $a, UniversalFilterNode $b, $strictSmaller=true,  $isAllFilter=true){
        $aggr = ($isAllFilter?AggregatorFunction::$AGGREGATOR_MIN:AggregatorFunction::$AGGREGATOR_MAX);
        $function = ($strictSmaller?BinaryFunction::$FUNCTION_BINARY_COMPARE_SMALLER_THAN:BinaryFunction::$FUNCTION_BINARY_COMPARE_SMALLER_OR_EQUAL_THAN);
        return new BinaryFunction($function, $a, new AggregatorFunction($aggr, $b));
    }
    
    /**
     * Creates a larger (or equal) than ALL/ANY  filter
     * 
     * @param UniversalFilterNode $a the left side
     * @param UniversalFilterNode $b the right side
     * @param boolean $strictLarger >= or >
     * @param boolean $isAllFilter ALL or ANY ?
     * @return NormalFilterNode the filter
     */
    public static function makeLargerThanAllOrAnyFilter(UniversalFilterNode $a, UniversalFilterNode $b, $strictLarger=true,  $isAllFilter=true){
        $aggr = ($isAllFilter?AggregatorFunction::$AGGREGATOR_MAX:AggregatorFunction::$AGGREGATOR_MIN);
        $function = ($strictLarger?BinaryFunction::$FUNCTION_BINARY_COMPARE_LARGER_THAN:BinaryFunction::$FUNCTION_BINARY_COMPARE_LARGER_OR_EQUAL_THAN);
        return new BinaryFunction($function, $a, new AggregatorFunction($aggr, $b));
    }
}

?>
