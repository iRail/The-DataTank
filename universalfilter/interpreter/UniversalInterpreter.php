<?php

/**
 * The UniversalInterpreter: 
 * Create an instance of this class and give it a query-tree execute the filter.
 *
 * @package The-Datatank/universalfilter/interpreter
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jeroen Penninck
 */

include_once("universalfilter/interpreter/IInterpreter.class.php");
include_once("universalfilter/interpreter/Environment.class.php");

include_once("universalfilter/interpreter/executers/UniversalFilterExecuters.php");

/**
 * Description of UniversalInterpreter
 *
 * @author Jeroen
 */
class UniversalInterpreter implements IInterpreter{
    
    private $executers;
    private $tablemanager;
    
    public function __construct() {
        $this->tablemanager=new UniversalFilterTableManager();
        
        $this->executers = array(
            "IDENTIFIER" => "IdentifierExecuter",
            "CONSTANT" => "ConstantExecuter",
            "FILTERCOLUMN" => "ColumnSelectionFilterExecuter",
            "FILTEREXPRESSION" => "FilterByExpressionExecuter",
            UnairyFunction::$FUNCTION_UNAIRY_UPPERCASE => "UnaryFunctionUppercaseExecuter",
            UnairyFunction::$FUNCTION_UNAIRY_LOWERCASE => "UnaryFunctionLowercaseExecuter",
            UnairyFunction::$FUNCTION_UNAIRY_STRINGLENGTH => "UnaryFunctionStringLengthExecuter",
            UnairyFunction::$FUNCTION_UNAIRY_ROUND => "UnaryFunctionRoundExecuter",
            UnairyFunction::$FUNCTION_UNAIRY_ISNULL => "UnaryFunctionIsNullExecuter",
            BinaryFunction::$FUNCTION_BINARY_PLUS => "BinaryFunctionPlusExecuter",
            BinaryFunction::$FUNCTION_BINARY_MINUS => "BinaryFunctionMinusExecuter",
            BinaryFunction::$FUNCTION_BINARY_MULTIPLY => "BinaryFunctionMultiplyExecuter",
            BinaryFunction::$FUNCTION_BINARY_DIVIDE => "BinaryFunctionDivideExecuter",
            BinaryFunction::$FUNCTION_BINARY_COMPARE_EQUAL => "BinaryFunctionEqualityExecuter",
            BinaryFunction::$FUNCTION_BINARY_COMPARE_SMALLER_THAN => "BinaryFunctionSmallerExecuter",
            BinaryFunction::$FUNCTION_BINARY_COMPARE_LARGER_THAN => "BinaryFunctionLargerExecuter",
            BinaryFunction::$FUNCTION_BINARY_COMPARE_SMALLER_OR_EQUAL_THAN => "BinaryFunctionSmallerEqualExecuter",
            BinaryFunction::$FUNCTION_BINARY_COMPARE_LARGER_OR_EQUAL_THAN => "BinaryFunctionLargerEqualExecuter",
            BinaryFunction::$FUNCTION_BINARY_COMPARE_NOTEQUAL => "BinaryFunctionNotEqualExecuter",
            BinaryFunction::$FUNCTION_BINARY_OR => "BinaryFunctionOrExecuter",
            BinaryFunction::$FUNCTION_BINARY_AND => "BinaryFunctionAndExecuter",
            BinaryFunction::$FUNCTION_BINARY_MATCH_REGEX => "BinaryFunctionMatchRegexExecuter",
            AggregatorFunction::$AGGREGATOR_AVG => "",
            AggregatorFunction::$AGGREGATOR_COUNT => "",
            AggregatorFunction::$AGGREGATOR_FIRST => "",
            AggregatorFunction::$AGGREGATOR_LAST => "",
            AggregatorFunction::$AGGREGATOR_MAX => "",
            AggregatorFunction::$AGGREGATOR_MIN => "",
            AggregatorFunction::$AGGREGATOR_SUM => ""
        );
    }
    
    public function findExecuterFor(UniversalFilterNode $filternode) {
        return new $this->executers[$filternode->getType()]();
    }
    
    public function getTableManager() {
        return $this->tablemanager;
    }
    
    public function interpret(UniversalFilterNode $tree){
        $executer = $this->findExecuterFor($tree);
        $env = $executer->execute($tree, $this);
        
        return $env->getTable();
    }
}

?>
