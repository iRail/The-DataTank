<?php


/**
 * This file contains all evaluators for aggregators
 * 
 * @package The-Datatank/universalfilter/interpreter/executers
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jeroen Penninck
 */

/* average */
class AverageAggregatorExecuter extends ColumnAggregatorFunctionExecuter {
    
    public function calculateValue(array $data){
        $sum = array_sum($data);
        $count = count($data);
        if($count==0) {return 0;}
        return $sum/$count;
    }
    
    public function keepFullInfo(){
        return false;
    }
    
    public function getName($name){
        return "avg_".$name;
    }
    
    public function errorIfNoItems(){
        return false;
    }
}

/* max */
class MaxAggregatorExecuter extends ColumnAggregatorFunctionExecuter {
    
    public function calculateValue(array $data){
        return max($data);
    }
    
    public function keepFullInfo(){
        return false;
    }
    
    public function getName($name){
        return "max_".$name;
    }
    
    public function errorIfNoItems(){
        return true;
    }
}

/* min */
class MinAggregatorExecuter extends ColumnAggregatorFunctionExecuter {
    
    public function calculateValue(array $data){
        return min($data);
    }
    
    public function keepFullInfo(){
        return false;
    }
    
    public function getName($name){
        return "min_".$name;
    }
    
    public function errorIfNoItems(){
        return true;
    }
}

/* sum */
class SumAggregatorExecuter extends ColumnAggregatorFunctionExecuter {

    public function calculateValue(array $data){
        return array_sum($data);
    }
    
    public function keepFullInfo(){
        return false;
    }
    
    public function getName($name){
        return "sum_".$name;
    }
    
    public function errorIfNoItems(){
        return false;
    }
}



/* first */
class FirstAggregatorExecuter extends ColumnAggregatorFunctionExecuter {
    
    public function calculateValue(array $data){
        return $data[0];
    }
    
    public function errorIfNoItems(){
        return true;
    }
}

/* last */
class LastAggregatorExecuter extends ColumnAggregatorFunctionExecuter {
    
    public function calculateValue(array $data){
        return $data[count($data)-1];
    }
    
    public function errorIfNoItems(){
        return true;
    }
}



/* count */
class CountAggregatorExecuter extends FullTableAggregatorFunctionExecuter {
    
    public function getName($name){
        return "count_".$name;
    }
    
    public function calculateValueForTable(UniversalFilterTableContent $content) {
        return $content->getRowCount();
    }
    
    public function keepFullInfo(){
        return false;
    }
}
?>
