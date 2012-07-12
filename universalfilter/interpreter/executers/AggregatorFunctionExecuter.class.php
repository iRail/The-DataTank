<?php
/**
 * This file contains the abstact top class for all aggregators
 *
 * @package The-Datatank/universalfilter
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jeroen Penninck
 */
abstract class AggregatorFunctionExecuter extends ExpressionNodeExecuter {
    
    public function evaluateAsExpressionHeader(UniversalFilterNode $filter, Environment $topenv, IInterpreter $interpreter){
//        $table1header = $this->getHeaderFor($filter->getArgument1(), $topenv, $interpreter);
//        $table2header = $this->getHeaderFor($filter->getArgument2(), $topenv, $interpreter);
//        
//        $combinedName = $this->getName(
//                $table1header->getColumnName(), 
//                $table2header->getColumnName());
//        
//        $isSingleRowByConstruction = $table1header->isSingleRowByConstruction() && $table2header->isSingleRowByConstruction();
//        
//        return new UniversalFilterTableHeader(array($combinedName), array(), $isSingleRowByConstruction, true);
    }
    
    public function evaluateAsExpression(UniversalFilterNode $filter, Environment $topenv, IInterpreter $interpreter) {
//        $arg1 = $filter->getArgument1();
//        $arg2 = $filter->getArgument2();
//        
//        $argExecuter1 = $interpreter->findExecuterFor($arg1);
//        $argExecuter2 = $interpreter->findExecuterFor($arg2);
//        
//        $table1header = $argExecuter1->evaluateAsExpressionHeader($arg1, $topenv, $interpreter);
//        $table2header = $argExecuter2->evaluateAsExpressionHeader($arg2, $topenv, $interpreter);
//        
//        $nameA = $table1header->getColumnName();
//        $nameB = $table2header->getColumnName();
//        $combinedName = $this->getName($nameA, $nameB);
//        
//        $table1content = $argExecuter1->evaluateAsExpression($arg1, $topenv, $interpreter);
//        $table2content = $argExecuter2->evaluateAsExpression($arg2, $topenv, $interpreter);
//        
//        if(!$table1header->isSingleRowByConstruction() && 
//                !$table2header->isSingleRowByConstruction() &&
//                $table1content->getRowCount()!=$table2content->getRowCount()){
//            throw new Exception("Columns differ in size");//TODO we should check if the columns are from the same table!!!!
//        }
//        
//        $rows=array();
//        
//        $size=max(array($table1content->getRowCount(), $table2content->getRowCount()));
//        
//        //loop through all rows and evaluate the expression
//        for ($i=0;$i<$size;$i++){
//            $row=new UniversalFilterTableContentRow();
//            
//            //get the value for index i for both tables
//            $valueA=$table1content->getValue($nameA, 0);
//            $valueB=$table2content->getValue($nameB, 0);
//            if($table1content->getRowCount()>$i){
//                $valueA=$table1content->getValue($nameA, $i);
//            }
//            if($table2content->getRowCount()>$i){
//                $valueB=$table2content->getValue($nameB, $i);
//            }
//            
//            //evaluate
//            $value = doBinaryFunction($valueA, $valueB);
//            
//            $row->defineValue($combinedName, $value);
//            
//            array_push($rows, $row);
//        }
//        
//        //return the result
//        return new UniversalFilterTableContent($rows);
    }
    
    
    
//    public function getName($nameA, $nameB){
//        return $nameA." combined ".$nameA;
//    }
//    
//    public function doBinaryFunction($valueA, $valueB){
//        return null;
//    }
}

?>
