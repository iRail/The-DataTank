<?php
/**
 * This file contains the abstact top class for all aggregators
 * 
 * The filter inside the aggregator gets executed row by row
 *
 * @package The-Datatank/universalfilter
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jeroen Penninck
 */
abstract class AggregatorFunctionExecuter extends ExpressionNodeExecuter {
    
    protected $filter;
    
    protected $header;
    
    protected $header1;
    
    protected $singleColumnSingleRow;
    
    //private
    private $executer1;
    private $evaluatorTable;
    private $topenv;

    
    public function initExpression(UniversalFilterNode $filter, Environment $topenv, IInterpreter $interpreter){
        $this->filter = $filter;
        
        $this->executer1 = $interpreter->findExecuterFor($this->filter->getColumn());
        
        
        //
        // Evaluate the header of the filter inside this aggregator...
        //  (evaluation need to be done for each row)
        //
        
        //check if header1 returns isSingleRow if we give it a single row
        $evaluatorEnvironment=$topenv->newModifiableEnvironment();
        //single row header
        $evaluatorHeader = $topenv->getTable()->getHeader()->cloneHeader();
        $evaluatorHeader->setIsSingleRowByConstruction(true);
        //single row content
        $evaluatorContent = new UniversalFilterTableContent();
        $evaluatorContent->addRow(new UniversalFilterTableContentRow());
        //single row table
        $this->evaluatorTable = new UniversalFilterTable($evaluatorHeader, $evaluatorContent);
        //single row environment
        $evaluatorEnvironment->setTable($this->evaluatorTable);
        
        //init executer
        $this->executer1->initExpression($this->filter->getColumn(), $evaluatorEnvironment, $interpreter);
        
        //check executer header
        $evaluatedHeader = $this->executer1->getExpressionHeader();
        if(!$evaluatedHeader->isSingleRowByConstruction()){
            throw new Exception("That function can not be used inside an aggregator!");
            //Could do a fall back -> BUT: In that case the thing inside this filter can have no dependencies on whatever we are calculating on. (It loads a new table...)
        }
        
        // header for the executer, as seen by the classes that override this class. (what you would expect as header)
        // not the same as the evaluatedHeader, as we execute it row by row...
        $globalHeader = $evaluatedHeader->cloneHeader();
        $globalHeader->setIsSingleRowByConstruction($topenv->getTable()->getHeader()->isSingleRowByConstruction());

        //set the seen header
        $this->header1 = $globalHeader;
        
        //save context for content-generation
        $this->topenv = $topenv;
    }
    
    /**
     * Evaluates the subfilter for each row. (neccessary for SELECTS in AVG in SELECT)
     * 
     * @return UniversalFilterTableContent 
     */
    protected function evaluateSubExpression(){
        $context = $this->topenv->getTable()->getContent();
        
        $newContent = new UniversalFilterTableContent();
        
        
        for ($index = 0; $index < $context->getRowCount(); $index++) {
            $contextRow = $context->getRow($index);
            
            $this->evaluatorTable->getContent()->setRow(0,$contextRow);
            
            $newRow = $this->executer1->evaluateAsExpression()->getRow(0);
            $newContent->addRow($newRow);
        }
        
        return $newContent;
    }
    
    /**
     * Converts a column to an array to make it easier to process 
     * 
     * @todo TODO: What if big table => should NOT convert to array. BUT have to write all aggregators manually... (can not use array_sum, count, max, min, ...)
     * @param UniversalFilterTableContent $content
     * @param type $columnId
     * @return array 
     */
    public function convertColumnToArray(UniversalFilterTableContent $content, $columnId){
        $arr = array();
        for ($index = 0; $index < $content->getRowCount(); $index++) {
            array_push($arr, $content->getRow($index)->getCellValue($columnId));
        }
        return $arr;
    }
    
    public function getExpressionHeader(){
        return $this->header;
    }
    
    public function evaluateAsExpression() {
        //need to be overriden
    }
    
    
    //
    // (Most of) these methods need to be overriden by subclasses
    //
    
    public function getName($name){
        return $name;
    }
    
    public function calculateValue(array $values){
        return 0;
    }
    
    public function calculateValueForTable(UniversalFilterTableContent $content){
        return 0;
    }
    
    public function keepFullInfo(){
        return true;
    }
}

?>
