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
class TableAliasExecuter extends UniversalFilterNodeExecuter {
    
    private $executer;
    private $header;
    
    public function initExpression(UniversalFilterNode $filter, Environment $topenv, IInterpreter $interpreter){
        $this->executer = $interpreter->findExecuterFor($filter->getSource());
        $this->executer->initExpression($filter->getSource(), $topenv, $interpreter);
        
        $this->header = $this->executer->getExpressionHeader()->cloneHeader();
        $this->header->renameAlias($filter->getAlias());
    }
    
    public function getExpressionHeader(){
        return $this->header;
    }
    
    public function evaluateAsExpression() {
        return $this->executer->evaluateAsExpression();
    }

}

?>
