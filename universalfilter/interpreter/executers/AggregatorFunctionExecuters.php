<?php


/**
 * This file contains all evaluators for aggregators
 * 
 * @package The-Datatank/universalfilter/interpreter/executers
 * @copyright (C) 2012 We Open Data
 * @license AGPLv3
 * @author Jeroen Penninck
 */

/* average */
class AverageAggregatorExecuter extends AggregatorFunctionExecuter {
    
    public function initExpression(UniversalFilterNode $filter, Environment $topenv, IInterpreter $interpreter) {
        parent::initExpression($filter, $topenv, $interpreter);
        
        $this->makeAllColumnsHeader();
    }
    
    public function evaluateAsExpression() {
        parent::evaluateAsExpression();
        
        return $this->callForAllColumns();
    }
    
    public function getName($name){
        return "avg_".$name;
    }
    
    public function calculateValue(array $data){
        return array_sum($data)/count($data);
    }
    
    public function keepFullInfo(){
        return false;
    }
}

/* max */
class MaxAggregatorExecuter extends AggregatorFunctionExecuter {
    
    public function initExpression(UniversalFilterNode $filter, Environment $topenv, IInterpreter $interpreter) {
        parent::initExpression($filter, $topenv, $interpreter);
        
        $this->makeAllColumnsHeader();
    }
    
    public function evaluateAsExpression() {
        parent::evaluateAsExpression();
        
        return $this->callForAllColumns();
    }
    
    public function getName($name){
        return "max_".$name;
    }
    
    public function calculateValue(array $data){
        return max($data);
    }
    
    public function keepFullInfo(){
        return false;
    }
}

/* min */
class MinAggregatorExecuter extends AggregatorFunctionExecuter {
    
    public function initExpression(UniversalFilterNode $filter, Environment $topenv, IInterpreter $interpreter) {
        parent::initExpression($filter, $topenv, $interpreter);
        
        $this->makeAllColumnsHeader();
    }
    
    public function evaluateAsExpression() {
        parent::evaluateAsExpression();
        
        return $this->callForAllColumns();
    }
    
    public function getName($name){
        return "min_".$name;
    }
    
    public function calculateValue(array $data){
        return min($data);
    }
    
    public function keepFullInfo(){
        return false;
    }
}

/* sum */
class SumAggregatorExecuter extends AggregatorFunctionExecuter {
    
    public function initExpression(UniversalFilterNode $filter, Environment $topenv, IInterpreter $interpreter) {
        parent::initExpression($filter, $topenv, $interpreter);
        
        $this->makeAllColumnsHeader();
    }
    
    public function evaluateAsExpression() {
        parent::evaluateAsExpression();
        
        return $this->callForAllColumns();
    }
    
    public function getName($name){
        return "sum_".$name;
    }
    
    public function calculateValue(array $data){
        return array_sum($data);
    }
    
    public function keepFullInfo(){
        return false;
    }
}



/* first */
class FirstAggregatorExecuter extends AggregatorFunctionExecuter {
    
    public function initExpression(UniversalFilterNode $filter, Environment $topenv, IInterpreter $interpreter) {
        parent::initExpression($filter, $topenv, $interpreter);
        
        $this->makeAllColumnsHeader();
    }
    
    public function evaluateAsExpression() {
        parent::evaluateAsExpression();
        
        return $this->callForAllColumns();
    }
    
//    public function getName($name){
//        return "first_".$name;
//    }
    
    public function calculateValue(array $data){
        return $data[0];
    }
    
    public function keepFullInfo(){
        return true;
    }
}

/* last */
class LastAggregatorExecuter extends AggregatorFunctionExecuter {
    
    public function initExpression(UniversalFilterNode $filter, Environment $topenv, IInterpreter $interpreter) {
        parent::initExpression($filter, $topenv, $interpreter);
        
        $this->makeAllColumnsHeader();
    }
    
    public function evaluateAsExpression() {
        parent::evaluateAsExpression();
        
        return $this->callForAllColumns();
    }
    
//    public function getName($name){
//        return "last_".$name;
//    }
    
    public function calculateValue(array $data){
        return $data[count($data)-1];
    }
    
    public function keepFullInfo(){
        return true;
    }
}



/* count */
class CountAggregatorExecuter extends AggregatorFunctionExecuter {
    
    public function initExpression(UniversalFilterNode $filter, Environment $topenv, IInterpreter $interpreter) {
        parent::initExpression($filter, $topenv, $interpreter);
        
        $this->makeSingleColumnHeader();
    }
    
    public function evaluateAsExpression() {
        parent::evaluateAsExpression();
        
        return $this->callSingleColumn();
    }
    
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
