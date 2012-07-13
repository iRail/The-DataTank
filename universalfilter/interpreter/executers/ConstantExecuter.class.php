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
    
    private $filter;
    
    private $header;
    
    private $const;
    private $nameOfField;
    
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
    
    public function initExpression(UniversalFilterNode $filter, Environment $topenv, IInterpreter $interpreter){
        $this->filter = $filter;
        
        $this->const=$this->getStripedConstant($filter);
        $this->nameOfField=$this->getFieldName($const);
        
        //column
        $cominedHeaderColumn = new UniversalFilterTableHeaderColumnInfo(aaray($nameOfField));
        
        //new Header
        $this->header = new UniversalFilterTableHeader(array($cominedHeaderColumn), true, true);
    }
    
    public function getExpresionHeader(){
        return $this->header;
    }
    
    public function evaluateAsExpression() {
        $id = $this->header->getColumnId();
        
        $row=new UniversalFilterTableContentRow();
        $row->defineValue($id, $const);
        
        return new UniversalFilterTableContent(array($row));
    }
    
    
    public function execute(UniversalFilterNode $filter, IInterpreter $interpreter) {
        throw new Exception("A constant can not be executed. Illegal filtertree.");
    }
}

?>
