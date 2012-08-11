<?php

/**
 * This file contains the abstact top class for all aggregators which convert a complete table into a single value!
 *
 * @package The-Datatank/universalfilter
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jeroen Penninck
 */
abstract class FullTableAggregatorFunctionExecuter extends AggregatorFunctionExecuter {

    private function makeSingleColumnHeader(){
        $name="";
        if($this->header1->isSingleColumnByConstruction()){
            $columnId = $this->header1->getColumnId();
            $columnInfo = $this->header1->getColumnInformationById($columnId);
            
            $name=$columnInfo->getName();
        }else{
            $name="_multiple_columns_";
        }
        
        $this->header = new UniversalFilterTableHeader(
                array(new UniversalFilterTableHeaderColumnInfo(array($this->getName($name))))
                , true, true);
    }
    
    public function callSingleColumn(){
        $oldContent = $this->evaluateSubExpression();
        $newContent = new UniversalFilterTableContent();
        
        $finalid = $this->header->getColumnId();
        
        $newRow = new UniversalFilterTableContentRow();
        $newRow->defineValue($finalid, $this->calculateValueForTable($oldContent));
        
        $newContent->addRow($newRow);
        
        $oldContent->tryDestroyTable();
        
        return $newContent;
    }
    
    public function initExpression(UniversalFilterNode $filter, Environment $topenv, IInterpreter $interpreter) {
        parent::initExpression($filter, $topenv, $interpreter);
        $this->makeSingleColumnHeader();
    }
    
    public function evaluateAsExpression() {
        return $this->callSingleColumn();
    }
    
    public function allowMultipleColumns(){
        return true;
    }
    
}

?>
