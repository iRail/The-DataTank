<?php
/**
 * This file contains an uniform representation of a query tree
 *
 * @package The-Datatank/universalfilter
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jeroen Penninck
 */

/**
 * Top class of all filters
 */
abstract class UniversalFilterNode {
    private $type;
    
    public function __construct($type) {
        $this->type=$type;
    }
    
    public function getType() {
        return $this->type;
    }
}

/**
 * Represents an identifier... 
 *
 * ... of a Table, a Column or an Alias of one of the two.
 *
 * format: 
 * - package.package.resource
 * - package.package.resource.name_of_column
 * - alias.name_of_column
 */
class Identifier extends UniversalFilterNode {
    private $value;//type:String
    
    public function __construct($value) {
        parent::__construct("IDENTIFIER");
        $this->value=$value;
    }
    
    public function getIdentifierString() {
        return $this->value;
    }
}

/**
 * Represents a constant
 * Can be a string, a boolean, or a number.
 */
class Constant extends UniversalFilterNode {
    private $constant;//type:String
    
    public function __construct($constant) {
        parent::__construct("CONSTANT");
        $this->constant=$constant;
    }
    
    public function getConstant() {
        return $this->constant;
    }
}

/**
 * Top class of all real filters
 * - all these filters have a source
 *  -> you can use putBefore or putAfter to put a filter on the beginning 
 *     or the end of the list
 */
abstract class NormalFilterNode extends UniversalFilterNode {
    private $source;//type:UniversalFilterNode
    
    public function setSource(UniversalFilterNode $source){
        $this->source=$source;
    }
    
    public function getSource(){
        return $this->source;
    }
}

/**
 * Represents an alias
 * Has a source and 
 */
class TableAliasFilter extends NormalFilterNode {
    private $alias;//type:String
    
    public function __construct($alias) {
        parent::__construct("TABLEALIAS");
        $this->alias=$alias;
    }
    
    public function getAlias() {
        return $this->alias;
    }
}

/**
 * Represents a filter that keeps the row if expression results in true
 * expression is a filter too.
 *
 * type: Table -> Table
 * type: GroupedTable -> GroupedTable
 *
 * aka "WHERE" or "HAVING"
 */
class FilterByExpressionFilter extends NormalFilterNode{
    private $expression;//type:UniversalFilterNode
    
    public function __construct(UniversalFilterNode $expression) {
        parent::__construct("FILTEREXPRESSION");
        $this->expression=$expression;
    }
    
    public function getExpression(){
        return $this->expression;
    }
}

/**
 * Represents a filter that filters columns by applying the filters in $columndata 
 * on each row of source and exprecting back one field of each filter
 *
 * If applied on grouped data some fields in the given row contain a table itself.
 *
 * As a result, the resulting table is never grouped.
 * 
 * Table aliases will be removed when executing this filter
 * 
 * type: Table -> Table
 * type: GroupedTable -> Table
 *
 * aka "SELECT"
 */
class ColumnSelectionFilter extends NormalFilterNode {
    private $columndata;//type:Array[ColumnSelectionFilterColumn]

    public function __construct(array /* of ColumnSelectionFilterColumn */ $columndata) {
        parent::__construct("FILTERCOLUMN");
        $this->columndata=$columndata;
    }
    
    public function getColumnData(){
        return $this->columndata;
    }
}

/** Represents a column used in the ColumnSelectionFilter */
class ColumnSelectionFilterColumn {
    private $column;//type:UniversalFilterNode
    private $alias;//type:String (can be null)

    public function __construct(UniversalFilterNode $column, $alias=null) {
        $this->column=$column;
        $this->alias=$alias;
    }
    
    public function getColumn(){
        return $this->column;
    }
    
    public function getAlias(){
        return $this->alias;
    }
}

/**
 * Represents a distinct filter => keeps only the rows that are distinct
 *
 * type: Table -> Table
 *
 * aka "DISTINCT"
 */
class DistinctFilter extends NormalFilterNode{
    public function __construct() {
        parent::__construct("FILTERDISTINCT");
    }
}


/**
 * Groups the data (not really a filter)
 * When the data is grouped, it can not be grouped again.
 * Futhermore it can be filtered only by a select number of filters:
 *  - FilterByExpression
 *  - ColumnSelectionFilter (after this node the data is ungrouped again)
 *
 * type: Table -> GroupedTable
 *
 * aka "GROUP BY"
 */
class DataGrouper extends NormalFilterNode {
    private $columns;
    
    public function __construct(array $columns) {
        parent::__construct("DATAGROUPER");
        $this->columns=$columns;
    }
    
    public function getColumns(){
        return $this->columns;
    }
}


/**
 * Top class of all functions
 * - all these functions need to get an environment to be able to execute
 */
abstract class ExpressionNode extends UniversalFilterNode {}


/*
 * 
 *  --- FUNCTIONS --- 
 * 
 */

//TODO (?): format()? now()?



/**
 * This class represents all unairy functions
 * 
 * type: Column -> Column
 * type: Cell -> Cell
 */
class UnairyFunction extends ExpressionNode {
    private $column;
    
    public static $FUNCTION_UNAIRY_UPPERCASE="FUNCTION_UNAIRY_UPPERCASE";
    public static $FUNCTION_UNAIRY_LOWERCASE="FUNCTION_UNAIRY_LOWERCASE";
    public static $FUNCTION_UNAIRY_STRINGLENGTH="FUNCTION_UNAIRY_STRINGLENGTH";
    public static $FUNCTION_UNAIRY_ROUND="FUNCTION_UNAIRY_ROUND";
    public static $FUNCTION_UNAIRY_ISNULL="FUNCTION_UNAIRY_ISNULL";
    public static $FUNCTION_UNAIRY_NOT="FUNCTION_UNAIRY_NOT";
    
    public function __construct($kind, UniversalFilterNode $column) {
        parent::__construct($kind);
        $this->column=$column;
    }
    
    public function getArgument(){
        return $this->column;
    }
}

/**
 * This class represents all binary functions
 * 
 * type: (Column,Column) -> Column
 * type: (Cell, Cell) -> Cell
 */
class BinaryFunction extends ExpressionNode {
    private $columnA;
    private $columnB;
    
    public static $FUNCTION_BINARY_PLUS="FUNCTION_BINARY_PLUS";
    public static $FUNCTION_BINARY_MINUS="FUNCTION_BINARY_MINUS";
    public static $FUNCTION_BINARY_MULTIPLY="FUNCTION_BINARY_MULTIPLY";
    public static $FUNCTION_BINARY_DIVIDE="FUNCTION_BINARY_DIVIDE";
    public static $FUNCTION_BINARY_COMPARE_EQUAL="FUNCTION_BINARY_COMPARE_EQUAL";
    public static $FUNCTION_BINARY_COMPARE_SMALLER_THAN="FUNCTION_BINARY_COMPARE_SMALLER_THAN";
    public static $FUNCTION_BINARY_COMPARE_LARGER_THAN="FUNCTION_BINARY_COMPARE_LARGER_THAN";
    public static $FUNCTION_BINARY_COMPARE_LARGER_OR_EQUAL_THAN="FUNCTION_BINARY_COMPARE_LARGER_OR_EQUAL_THAN";
    public static $FUNCTION_BINARY_COMPARE_SMALLER_OR_EQUAL_THAN="FUNCTION_BINARY_COMPARE_SMALLER_OR_EQUAL_THAN";
    public static $FUNCTION_BINARY_COMPARE_NOTEQUAL="FUNCTION_BINARY_COMPARE_NOTEQUAL";
    public static $FUNCTION_BINARY_OR="FUNCTION_BINARY_OR";
    public static $FUNCTION_BINARY_AND="FUNCTION_BINARY_AND";
    public static $FUNCTION_BINARY_MATCH_REGEX="FUNCTION_BINARY_MATCH_REGEX";// does $1 matches $2 ? ($2 is in php regex format!)
    
    public function __construct($kind, UniversalFilterNode $columnA, UniversalFilterNode $columnB) {
        parent::__construct($kind);
        $this->columnA=$columnA;
        $this->columnB=$columnB;
    }
    
    public function getArgument1(){
        return $this->columnA;
    }
    
    public function getArgument2(){
        return $this->columnB;
    }
}

/**
 * This class represents all tertairy functions
 * 
 * type: (Column,Column,Column) -> Column
 * type: (Cell, Cell, Cell) -> Cell
 */
class TertairyFunction extends ExpressionNode {
    private $columnA;
    private $columnB;
    private $columnC;
    
    public static $FUNCTION_TERTIARY_SUBSTRING="FUNCTION_TERTIARY_SUBSTRING";//get part of $1 from index $2 with length $3
    public static $FUNCTION_TERTIARY_REGEX_REPLACE="FUNCTION_TERTIARY_REGEX_REPLACE";//replace $1 by $2 in $3
    
    public function __construct($kind, UniversalFilterNode $columnA, UniversalFilterNode $columnB, UniversalFilterNode $columnC) {
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
class AggregatorFunction extends ExpressionNode {
    private $column;
    
    public static $AGGREGATOR_AVG="AGGREGATOR_AVG";
    public static $AGGREGATOR_COUNT="AGGREGATOR_COUNT";
    public static $AGGREGATOR_FIRST="AGGREGATOR_FIRST";
    public static $AGGREGATOR_LAST="AGGREGATOR_LAST";
    public static $AGGREGATOR_MAX="AGGREGATOR_MAX";
    public static $AGGREGATOR_MIN="AGGREGATOR_MIN";
    public static $AGGREGATOR_SUM="AGGREGATOR_SUM";
    
    public function __construct($kind, UniversalFilterNode $column) {
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
 * type: [Column, [Constant, ...]] -> Column
 */
class CheckInFunction extends NormalFilterNode {
    private $constants;
    
    public static $FUNCTION_IN_LIST="FUNCTION_IN_LIST";// is a varargs function
    
    public function __construct(array /* of Constant */ $constants) {
        parent::__construct(CheckInFunction::$FUNCTION_IN_LIST);
        $this->constants=$constants;
    }
    
    public function getConstants(){
        return $this->constants;
    }
}


/**
 * Extre functions
 */
include_once("universalfilter/CombinedFilterGenerators.class.php");