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

    private $typeInlineSelect;
    
    public function initExpression(UniversalFilterNode $filter, Environment $topenv, IInterpreterControl $interpreter, $preferColumn){
        $this->filter = $filter;
        
        $this->executer1 = $interpreter->findExecuterFor($this->filter->getSource());
        
        
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
        $this->executer1->initExpression($this->filter->getSource(), $evaluatorEnvironment, $interpreter, true);
        
        //check executer header
        $evaluatedHeader = $this->executer1->getExpressionHeader();
        $this->typeInlineSelect = !$evaluatedHeader->isSingleRowByConstruction();
        if($this->typeInlineSelect){
            if(!UniversalInterpreter::$ALLOW_NESTED_QUERYS){
                throw new Exception("Nested Query's are disabled because of performance issues.");
            }
            if(!$evaluatedHeader->isSingleColumnByConstruction()){
                if(!$this->allowMultipleColumns()){
                    throw new Exception("If you use a columnSelectionFilter in a Aggregator, the columnSelectionFilter should only return 1 column.");
                }
            }
        }
        
        // header for the executer, as seen by the classes that override this class. (what you would expect as header)
        // not the same as the evaluatedHeader, as we execute it row by row...
        $singleRow = $topenv->getTable()->getHeader()->isSingleRowByConstruction();
        $globalHeader = $evaluatedHeader->cloneHeader();
        $globalHeader->setIsSingleRowByConstruction($singleRow);
        if($this->typeInlineSelect){//special header for inline select...
            $newColumns = array();
            
            for ($columnIndex = 0; $columnIndex < $globalHeader->getColumnCount(); $columnIndex++) {
                $columnId=$globalHeader->getColumnIdByIndex($columnIndex);
                $groupedHeaderColumn = $globalHeader->getColumnInformationById($columnId)->cloneColumnGrouped();

                array_push($newColumns, $groupedHeaderColumn);
            }
            $globalHeader = new UniversalFilterTableHeader($newColumns, $singleRow, true);
        }

        //set the seen header
        $this->header1 = $globalHeader;
        
        //save context for content-generation
        $this->topenv = $topenv;
    }
    
    public function allowMultipleColumns(){
        return true;
    }
    
    /**
     * Evaluates the subfilter for each row. (neccessary for SELECTS in AVG in SELECT)
     * 
     * @return UniversalFilterTableContent 
     */
    protected function evaluateSubExpression(){
        $context = $this->topenv->getTable()->getContent();
        $evaluatedHeader = $this->executer1->getExpressionHeader();
        
        $newContent = new UniversalFilterTableContent();
        
        for ($index = 0; $index < $context->getRowCount(); $index++) {

            $contextRow = $context->getRow($index);

            $this->evaluatorTable->getContent()->setRow(0,$contextRow);

            $executedContent = $this->executer1->evaluateAsExpression();

            if(!$this->typeInlineSelect){
                $newContent->addRow($executedContent->getRow(0));
            }else{
                $newRow=new UniversalFilterTableContentRow();
                for ($columnIndex = 0; $columnIndex < $this->header1->getColumnCount(); $columnIndex++) {
                    $newColumnId=$this->header1->getColumnIdByIndex($columnIndex);
                    $oldColumnId=$evaluatedHeader->getColumnIdByIndex($columnIndex);

                    $newRow->defineGroupedValue($newColumnId, $this->convertColumnToArray($executedContent, $oldColumnId));//TODO: is saving grouped values as an array a good idea?
                }
                
                $newContent->addRow($newRow);
            }
            $executedContent->tryDestroyTable();
        }
        
        $this->evaluatorTable->getContent()->tryDestroyTable();
        
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
