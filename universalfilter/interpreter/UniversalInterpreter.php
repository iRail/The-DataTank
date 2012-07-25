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

include_once("universalfilter/interpreter/IInterpreterControl.class.php");
include_once("universalfilter/interpreter/Environment.class.php");

include_once("universalfilter/interpreter/sourceusage/SourceUsageData.class.php");
include_once("universalfilter/interpreter/cloning/FilterTreeCloner.class.php");

include_once("universalfilter/interpreter/executers/UniversalFilterExecuters.php");

include_once("universalfilter/interpreter/optimizer/UniversalOptimizer.class.php");


class UniversalInterpreter implements IInterpreterControl{
    
    private $executers;
    private $tablemanager;
    
    /**
     * Are nested querys allowed?
     * true = yes, they are allowed.
     * false = no, throw an exception if you try to use them...
     * 
     * @var boolean 
     */
    public static $ALLOW_NESTED_QUERYS=false;
    
    /**
     * Constructor, fill the executer-class map.
     */
    public function __construct() {
        $this->tablemanager=new UniversalFilterTableManager();
        
        $this->executers = array(
            "IDENTIFIER" => "IdentifierExecuter",
            "CONSTANT" => "ConstantExecuter",
            "FILTERCOLUMN" => "ColumnSelectionFilterExecuter",
            "FILTEREXPRESSION" => "FilterByExpressionExecuter",
            "DATAGROUPER" => "DataGrouperExecuter",
            "TABLEALIAS" => "TableAliasExecuter",
            "FILTERDISTINCT" => "DistinctFilterExecuter",
            UnairyFunction::$FUNCTION_UNAIRY_UPPERCASE => "UnaryFunctionUppercaseExecuter",
            UnairyFunction::$FUNCTION_UNAIRY_LOWERCASE => "UnaryFunctionLowercaseExecuter",
            UnairyFunction::$FUNCTION_UNAIRY_STRINGLENGTH => "UnaryFunctionStringLengthExecuter",
            UnairyFunction::$FUNCTION_UNAIRY_ROUND => "UnaryFunctionRoundExecuter",
            UnairyFunction::$FUNCTION_UNAIRY_ISNULL => "UnaryFunctionIsNullExecuter",
            UnairyFunction::$FUNCTION_UNAIRY_NOT => "UnaryFunctionNotExecuter",
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
            TertairyFunction::$FUNCTION_TERTIARY_SUBSTRING => "TertairyFunctionSubstringExecuter",
            TertairyFunction::$FUNCTION_TERTIARY_REGEX_REPLACE => "TertairyFunctionRegexReplacementExecuter",
            AggregatorFunction::$AGGREGATOR_AVG => "AverageAggregatorExecuter",
            AggregatorFunction::$AGGREGATOR_COUNT => "CountAggregatorExecuter",
            AggregatorFunction::$AGGREGATOR_FIRST => "FirstAggregatorExecuter",
            AggregatorFunction::$AGGREGATOR_LAST => "LastAggregatorExecuter",
            AggregatorFunction::$AGGREGATOR_MAX => "MaxAggregatorExecuter",
            AggregatorFunction::$AGGREGATOR_MIN => "MinAggregatorExecuter",
            AggregatorFunction::$AGGREGATOR_SUM => "SumAggregatorExecuter",
            CheckInFunction::$FUNCTION_IN_LIST => "CheckInFunctionExecuter"
        );
    }
    
    public function findExecuterFor(UniversalFilterNode $filternode) {
        return new $this->executers[$filternode->getType()]();
    }
    
    public function getTableManager() {
        return $this->tablemanager;
    }
    
    public function interpret(UniversalFilterNode $originaltree){
        //CLONE QUERY (because we will modify it... and the caller might want to keep the original query)
        $cloner = new FilterTreeCloner();
        $clonedtree = $cloner->deepCopyTree($originaltree);
        
        //OPTIMIZE
        $optimizer = new UniversalOptimizer();
        
        $tree = $optimizer->optimize($clonedtree);
        
        //START ENVIRONMENT... is empty
        $emptyEnv = new Environment();
        $emptyEnv->setTable(new UniversalFilterTable(new UniversalFilterTableHeader(array(), true, false), new UniversalFilterTableContent()));
        
        //CALCULATE ALL HEADERS
        $executer = $this->findExecuterFor($tree);
        $executer->initExpression($tree, $emptyEnv, $this, false);
        
        //TRAVERSE TREE AND GIVE QUERYS TO SOURCES THEMSELF...
        // TODO: check headers for things that can be executed on a source...
        
        //BUILD A NEW QUERY WITH THE PRECALCULATED DATA
        
        
        //EXECUTE (for real this time) (note: recalculate headers also...)
        $executer = $this->findExecuterFor($tree);
        $executer->initExpression($tree, $emptyEnv, $this, false);
        
        //get the table, in two steps
        $header = $executer->getExpressionHeader();
        
        $content = $executer->evaluateAsExpression();
        
        
        //RETURN
        return new UniversalFilterTable($header, $content);
        
        //CLEANUP -> when you don't need the data anymore
        //$content->tryDestroyTable();
    }
}

?>
