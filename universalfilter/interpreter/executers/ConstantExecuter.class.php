<?php

/**
 * "Executes" a constant and returns a table
 * 
 * @package The-Datatank/universalfilter/interpreter/executers
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jeroen Penninck
 */
class ConstantExecuter extends UniversalFilterNodeExecuter {
    
    private function getStripedConstant(UniversalFilterNode $filter){
        $const = $filter->getConstant();
        if(substr($const,0,1)=="'"){
            $const = substr($const,1,-1);
        }
        return $const;
    }
    
    private function getFieldName($const){
        if($const!=""){
            return "$const";
        }else{
            return "empty";
        }
    }
    
    public function evaluateAsExpressionHeader(UniversalFilterNode $filter, Environment $topenv, IInterpreter $interpreter){
        $const=$this->getStripedConstant($filter);
        $nameOfField=$this->getFieldName($const);
        
        return new UniversalFilterTableHeader(array($nameOfField), array(), true, true);
    }
    
    public function evaluateAsExpression(UniversalFilterNode $filter, Environment $topenv, IInterpreter $interpreter) {
        $const=$this->getStripedConstant($filter);
        $nameOfField=$this->getFieldName($const);
        
        $row=new UniversalFilterTableContentRow();
        $row->defineValue($nameOfField, $const);
        
        return new UniversalFilterTableContent(array($row));
    }
    
    public function execute(UniversalFilterNode $filter, IInterpreter $interpreter) {
        throw new Exception("A constant can not be executed. Illegal filtertree.");
    }
}

?>
