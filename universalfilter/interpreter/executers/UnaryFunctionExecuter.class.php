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
        $this->executer2 = $interpreter->findExecuterFor($arg2);
        
        //init down
        $this->executer1->initExpression($this->filter->getArgument(), $topenv, $interpreter);
        
        $this->header1 = $this->executer1->getExpresionHeader();
        
        //combined name
        $combinedName = $this->getName(
                $table1header->getColumnNameById($this->header1->getColumnId()));
        
        //column
        $cominedHeaderColumn = new UniversalFilterTableHeaderColumnInfo(array($combinedName));
        
        //single row?
        $isSingleRowByConstruction = $this->header1->isSingleRowByConstruction();
        
        //new Header
        $this->header = new UniversalFilterTableHeader(array($cominedHeaderColumn), $isSingleRowByConstruction, true);
    }
    
    public function getExpresionHeader(){
        return $this->header;
    }
    
    public function evaluateAsExpression() {
        $table1content = $this->executer1->evaluateAsExpression();
        
        $idA = $this->header1->getColumnId();
        $finalid = $this->header->getColumnId();
        
        $rows=array();
        
        $size=$table1content->getRowCount();
        
        //loop through all rows and evaluate the expression
        for ($i=0;$i<$size;$i++){
            $row=new UniversalFilterTableContentRow();
            
            //get the value for index i for both tables
            $valueA=$table1content->getValue($idA, 0);
            
            //evaluate
            $value = $this->doUnaryFunction($valueA, $valueB);
            
            $row->defineValue($finalid, $value);
            
            array_push($rows, $row);
        }
        
        //return the result
        return new UniversalFilterTableContent($rows);
    }
    
    public function getName($name){
        return $name;
    }
    
    public function doUnaryFunction($value){
        return null;
    }
}


?>