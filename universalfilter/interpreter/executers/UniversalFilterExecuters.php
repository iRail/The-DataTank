<?php

/**
 * This file collects all imports for the UniversalInterpreter
 *
 * @package The-Datatank/universalfilter/interpreter/executers
 * @copyright (C) 2012 We Open Data
 * @license AGPLv3
 * @author Jeroen Penninck
 */

// base filters
include_once("universalfilter/interpreter/executers/UniversalFilterNodeExecuter.class.php");
include_once("universalfilter/interpreter/executers/IdentifierExecuter.class.php");
include_once("universalfilter/interpreter/executers/ConstantExecuter.class.php");
include_once("universalfilter/interpreter/executers/FilterByExpressionExecuter.class.php");
include_once("universalfilter/interpreter/executers/ColumnSelectionFilterExecuter.class.php");
include_once("universalfilter/interpreter/executers/DistinctFilterExecuter.class.php");
include_once("universalfilter/interpreter/executers/DataGrouperExecuter.class.php");

// expressions
include_once("universalfilter/interpreter/executers/ExpressionNodeExecuter.class.php");
include_once("universalfilter/interpreter/executers/UnaryFunctionExecuter.class.php");
include_once("universalfilter/interpreter/executers/UnaryFunctionExecuters.php");
include_once("universalfilter/interpreter/executers/BinaryFunctionExecuter.class.php");
include_once("universalfilter/interpreter/executers/BinaryFunctionExecuters.php");
include_once("universalfilter/interpreter/executers/TertairyFunctionExecuter.class.php");
include_once("universalfilter/interpreter/executers/TertairyFunctionExecuters.php");
include_once("universalfilter/interpreter/executers/AggregatorFunctionExecuter.class.php");
include_once("universalfilter/interpreter/executers/AggregatorFunctionExecuters.php");


/*
 *  Other specific functions
 */
 
/**
 * Checks if the value is in a list of constants
 * type: [Cell, [Constant, ...]] -> Cell
 */
class CheckInFunctionExecuter extends NormalFilterNode {
    private $constants;
    
    public static $FUNCTION_IN_LIST="FUNCTION_IN_LIST";// is a varargs function
    
    public function __construct($constants) {
        parent::__construct(FUNCTION_IN_LIST);
        $this->constants=$constants;
    }
    
    public function getConstants(){
        return $this->constants;
    }
}