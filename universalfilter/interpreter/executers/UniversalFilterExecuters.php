<?php

/**
 * This file collects all imports for the UniversalInterpreter
 *
 * @package The-Datatank/universalfilter/interpreter/executers
 * @copyright (C) 2012 by iRail vzw/asbl
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
//todo: aggregators


//TODO: Rest + TertairyFunctionExecuter

/**
 * This class represents all tertairy functions
 * 
 * type: (Column,Column,Column) -> Column
 * type: (Cell, Cell, Cell) -> Cell
 */
class TertairyFunctionExecuter extends ExpressionNodeExecuter {
    private $columnA;
    private $columnB;
    private $columnC;
    
    public static $FUNCTION_TERTIARY_SUBSTRING="FUNCTION_TERTIARY_SUBSTRING";
    public static $FUNCTION_TERTIARY_BETWEEN="FUNCTION_TERTIARY_BETWEEN";
    
    public function __construct($kind, UniversalFilterNodeExecuter $columnA, UniversalFilterNodeExecuter $columnB, UniversalFilterNodeExecuter $columnC) {
        parent::__construct($kind);
        $this->columnA=$columnA;
        $this->columnB=$columnB;
        $this->columnB=$columnC;
    }
    
    public function getArgument1(){
        return $this->columnA;
    }
    
    public function getArgument2(){
        return $this->columnB;
    }
    
    public function getArgument3(){
        return $this->columnC;
    }
}

/**
 * This class represents all aggregator functions
 * 
 * type: Column -> Cell
 */
class AggregatorFunctionExecuter extends ExpressionNodeExecuter {
    private $column;
    
    public static $AGGREGATOR_AVG="AGGREGATOR_AVG";
    public static $AGGREGATOR_COUNT="AGGREGATOR_COUNT";
    public static $AGGREGATOR_FIRST="AGGREGATOR_FIRST";
    public static $AGGREGATOR_LAST="AGGREGATOR_LAST";
    public static $AGGREGATOR_MAX="AGGREGATOR_MAX";
    public static $AGGREGATOR_MIN="AGGREGATOR_MIN";
    public static $AGGREGATOR_SUM="AGGREGATOR_SUM";
    
    public function __construct($kind, UniversalFilterNodeExecuter $column) {
        parent::__construct($kind);
        $this->column=$column;
    }
    
    public function getColumn(){
        return $this->column;
    }
}



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