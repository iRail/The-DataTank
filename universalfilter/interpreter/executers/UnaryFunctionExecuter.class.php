<?php
/**
 * This file contains the abstact top class for all evaluators for unary functions
 * 
 * @package The-Datatank/universalfilter/interpreter/executers
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jeroen Penninck
 */
class UnaryFunctionExecuter extends ExpressionNodeExecuter {
    
    private $filter;
    
    private $header;
    
    private $executer1;
    
    private $header1;
    
    public function initExpression(UniversalFilterNode $filter, Environment $topenv, IInterpreter $interpreter){
        $this->filter = $filter;
        
        $this->executer1 = $interpreter->findExecuterFor($this->filter->getArgument());
        
        //init
        $this->executer1->initExpression($this->filter->getArgument(), $topenv, $interpreter);
        
        $this->header1 = $this->executer1->getExpressionHeader();
        
        //combined name
        $combinedName = $this->getName(
                $this->header1->getColumnNameById($this->header1->getColumnId()));
        
        //column
        $cominedHeaderColumn = new UniversalFilterTableHeaderColumnInfo(array($combinedName));
        
        //single row?
        $isSingleRowByConstruction = $this->header1->isSingleRowByConstruction();
        
        //new Header
        $this->header = new UniversalFilterTableHeader(array($cominedHeaderColumn), $isSingleRowByConstruction, true);
    }
    
    public function getExpressionHeader(){
        return $this->header;
    }
    
    public function evaluateAsExpression() {
        $table1content = $this->executer1->evaluateAsExpression();
        
        $idA = $this->header1->getColumnId();
        $finalid = $this->header->getColumnId();
        
        $rows=new UniversalFilterTableContent();
        
        $size=$table1content->getRowCount();
        
        //loop through all rows and evaluate the expression
        for ($i=0;$i<$size;$i++){
            $row=new UniversalFilterTableContentRow();
            
            //get the value for index i
            $valueA=$table1content->getValue($idA, $i);
            
            //evaluate
            $value = $this->doUnaryFunction($valueA);
            
            $row->defineValue($finalid, $value);
            
            $rows->addRow($row);
        }
        
        //return the result
        return $rows;
    }
    
    public function getName($name){
        return $name;
    }
    
    public function doUnaryFunction($value){
        return null;
    }
}


?>