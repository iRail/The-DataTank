<?php

/**
 * Executes the FilterByExpression filter
 * 
 * @package The-Datatank/universalfilter/interpreter/executers
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jeroen Penninck
 */
class FilterByExpressionExecuter extends BaseEvaluationEnvironmentFilterExecuter {

    private $filter;
    private $interpreter;
    
    private $header;
    
    private $executer;
    
    private $childEnvironmentData;
    private $giveToColumnsEnvironment;
    
    public function initExpression(UniversalFilterNode $filter, Environment $topenv, IInterpreter $interpreter) {
        $this->filter = $filter;
        $this->interpreter = $interpreter;
        
        
        
        
        //get source environment header
        $executer = $interpreter->findExecuterFor($filter->getSource());
        $this->executer = $executer;
        
        
        $this->childEnvironmentData = $this->initChildEnvironment($filter, $topenv, $interpreter, $executer);
        $this->giveToColumnsEnvironment = $this->getChildEnvironment($this->childEnvironmentData);
        
        
        
        //
        // BUILD OWN HEADER
        //

        //create the new header
        //   -> It's the same as the source (we could copy it here...)
        $this->header=$this->executer->getExpressionHeader();
        
    }
    
    public function getExpressionHeader() {
        return $this->header;
    }
    
    public function evaluateAsExpression() {
        $sourceheader =$this->executer->getExpressionHeader();
        $sourcecontent=$this->executer->evaluateAsExpression();
        
        $this->finishChildEnvironment($this->childEnvironmentData);
        $this->giveToColumnsEnvironment->setTable(new UniversalFilterTable($sourceheader, $sourcecontent));
        
        // get executer for expression
        $expr = $this->filter->getExpression();
        $exprexec = $this->interpreter->findExecuterFor($expr);
        $exprexec->initExpression($expr, $this->giveToColumnsEnvironment, $this->interpreter);
        $exprheader = $exprexec->getExpressionHeader();
        
        // filter the content
        $filteredRows = new UniversalFilterTableContent();
        
        
        // calcultate the table with true and false
        $inResultTable = $exprexec->evaluateAsExpression();
        
        //loop all rows
        for ($index = 0; $index < $sourcecontent->getRowCount(); $index++) {
            $row = $sourcecontent->getRow($index);
            
            //get the right value in the result
            $anwser = null;
            if($index<$inResultTable->getRowCount()){
                $anwser = $inResultTable->getRow($index);
            }else{
                $anwser = $inResultTable->getRow(0);
            }
            
            //if the expression evaluates to true, then add the row
            if($anwser->getCellValue($exprheader->getColumnId())=="true"){
                $filteredRows->addRow($row);
            }
        }
        
        $inResultTable->tryDestroyTable();
        
        $sourcecontent->tryDestroyTable();
        
        return $filteredRows;
    }
}

?>
