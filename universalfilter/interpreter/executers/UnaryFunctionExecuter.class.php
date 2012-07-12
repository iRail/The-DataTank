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
    
    public function evaluateAsExpressionHeader(UniversalFilterNode $filter, Environment $topenv, IInterpreter $interpreter){
        $tableheader = $this->getHeaderFor($filter->getArgument(), $topenv, $interpreter);
        
        $combinedName = $this->getName($tableheader->getColumnName());
        
        return new UniversalFilterTableHeader(array($combinedName), array(), $tableheader->isSingleRowByConstruction(), true);
    }
    
    public function evaluateAsExpression(UniversalFilterNode $filter, Environment $topenv, IInterpreter $interpreter) {
        $arg = $filter->getArgument();
        
        $argExecuter = $interpreter->findExecuterFor($arg);
        
        $tableheader = $argExecuter->evaluateAsExpressionHeader($arg, $topenv, $interpreter);
        
        $name = $tableheader->getColumnName();
        
        $combinedName = $this->getName($name);
        
        $tablecontent = $argExecuter->evaluateAsExpression($arg, $topenv, $interpreter);
        
        $rows=array();
        
        $size=$tablecontent->getRowCount();
        
        //loop through all rows and evaluate the expression
        for ($i=0;$i<$size;$i++){
            $row=new UniversalFilterTableContentRow();
            
            //get the value for index i for the tables
            $value=$tablecontent->getValue($name, $i);
            
            //evaluate
            $newValue = $this->doUnaryFunction($value);
            
            $row->defineValue($combinedName, $newValue);
            
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