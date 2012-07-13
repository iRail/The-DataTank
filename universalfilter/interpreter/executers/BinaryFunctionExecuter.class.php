<?php
/**
 * This file contains the abstact top class for all evaluators for binary functions
 * 
 * @package The-Datatank/universalfilter/interpreter/executers
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jeroen Penninck
 */
abstract class BinaryFunctionExecuter extends ExpressionNodeExecuter {
    
    private $filter;
    
    private $header;
    
    private $executer1;
    private $executer2;
    
    private $header1;
    private $header2;
    
    public function initExpression(UniversalFilterNode $filter, Environment $topenv, IInterpreter $interpreter){
        $this->filter = $filter;
        
        $this->executer1 = $interpreter->findExecuterFor($this->filter->getArgument1());
        $this->executer2 = $interpreter->findExecuterFor($this->filter->getArgument2());
        
        //init down
        $this->executer1->initExpression($this->filter->getArgument1(), $topenv, $interpreter);
        $this->executer2->initExpression($this->filter->getArgument2(), $topenv, $interpreter);
        
        $this->header1 = $this->executer1->getExpresionHeader();
        $this->header2 = $this->executer2->getExpresionHeader();
        
        //combined name
        $combinedName = $this->getName(
                $table1header->getColumnNameById($this->header1->getColumnId()), 
                $table2header->getColumnNameById($this->header2->getColumnId()));
        
        //column
        $cominedHeaderColumn = new UniversalFilterTableHeaderColumnInfo(array($combinedName));
        
        //single row?
        $isSingleRowByConstruction = $this->header1->isSingleRowByConstruction() && $this->header2->isSingleRowByConstruction();
        
        //new Header
        $this->header = new UniversalFilterTableHeader(array($cominedHeaderColumn), $isSingleRowByConstruction, true);
    }
    
    public function getExpresionHeader(){
        return $this->header;
    }
    
    public function evaluateAsExpression() {
        $table1content = $this->executer1->evaluateAsExpression();
        $table2content = $this->executer2->evaluateAsExpression();
        
        $idA = $this->header1->getColumnId();
        $idB = $this->header1->getColumnId();
        $finalid = $this->header->getColumnId();
        
        if(
                !$this->header1->isSingleRowByConstruction() && 
                !$this->header2->isSingleRowByConstruction() &&
                $table1content->getRowCount()!=$table2content->getRowCount()){
            throw new Exception("Columns differ in size");//Can that happen??????????
        }
        
        $rows=array();
        
        $size=max(array($table1content->getRowCount(), $table2content->getRowCount()));
        
        //loop through all rows and evaluate the expression
        for ($i=0;$i<$size;$i++){
            $row=new UniversalFilterTableContentRow();
            
            //get the value for index i for both tables
            $valueA=$table1content->getValue($idA, 0);
            $valueB=$table2content->getValue($idB, 0);
            if($table1content->getRowCount()>$i){
                $valueA=$table1content->getValue($idA, $i);
            }
            if($table2content->getRowCount()>$i){
                $valueB=$table2content->getValue($idB, $i);
            }
            
            //evaluate
            $value = $this->doBinaryFunction($valueA, $valueB);
            
            $row->defineValue($finalid, $value);
            
            array_push($rows, $row);
        }
        
        //return the result
        return new UniversalFilterTableContent($rows);
    }
    
    
    
    public function getName($nameA, $nameB){
        return $nameA." combined ".$nameA;
    }
    
    public function doBinaryFunction($valueA, $valueB){
        return null;
    }
}
?>
