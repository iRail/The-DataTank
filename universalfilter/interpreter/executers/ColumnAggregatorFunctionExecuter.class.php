<?php

/**
 * This file contains the abstact top class for all aggregators which convert each column independently into a single value
 *
 * @package The-Datatank/universalfilter
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jeroen Penninck
 */
abstract class ColumnAggregatorFunctionExecuter extends AggregatorFunctionExecuter {

    
    private function makeAllColumnsHeader(){
        if($this->header1->isSingleColumnByConstruction()){
            //single column, may be grouped...
            $singleRow=true;
            
            
            $columnId = $this->header1->getColumnId();
            $columnInfo = $this->header1->getColumnInformationById($columnId);
            $columnName = $columnInfo->getName();
            
            if($columnInfo->isGrouped()){
                $singleRow=false;
            }

            $cominedHeaderColumn=null;
            if($this->keepFullInfo()){
                $cominedHeaderColumn = $columnInfo->cloneColumnInfo();
            }else{
                $combinedName = $this->getName($columnName);
                $cominedHeaderColumn = $columnInfo->cloneBaseUpon($combinedName);
            }
            $newColumns=array($cominedHeaderColumn);
            
            
            $this->header = new UniversalFilterTableHeader($newColumns, $singleRow, true);
            $this->singleColumnSingleRow=$singleRow;
            
        }else{
            //multiple columns -> grouping not allowed!
            
            $newColumns=array();
        
            for ($index = 0; $index < $this->header1->getColumnCount(); $index++) {
                $columnId = $this->header1->getColumnIdByIndex($index);
                $columnInfo = $this->header1->getColumnInformationById($columnId);
                $columnName = $columnInfo->getName();
                
                if($columnInfo->isGrouped()){
                    //Should never happen?
                    throw new Exception("This operation can not be used on multiple columns with grouping.");
                }

                $cominedHeaderColumn=null;
                if($this->keepFullInfo()){
                    $cominedHeaderColumn = $columnInfo->cloneColumnInfo();
                }else{
                    $combinedName = $this->getName($columnName);
                    $cominedHeaderColumn = $columnInfo->cloneBaseUpon($combinedName);
                }
                array_push($newColumns, $cominedHeaderColumn);
            }
            
            $this->header = new UniversalFilterTableHeader($newColumns, true, false);
        }
    }
    
    private function callForAllColumns(){
        $oldContent = $this->evaluateSubExpression();
        $newContent = new UniversalFilterTableContent();
        
        if($this->header1->isSingleColumnByConstruction()){
            $sourceColumnId = $this->header1->getColumnId();
            $finalid = $this->header->getColumnId();
            
            if($this->singleColumnSingleRow){
                //single column - not grouped
                $values=$this->convertColumnToArray($oldContent, $sourceColumnId);
                
                $row = new UniversalFilterTableContentRow();
                $row->defineValue($finalid, $this->doCalculate($values));
                
                $newContent->addRow($row);
            }else{
                //single column - grouped
                for ($index = 0; $index < $oldContent->getRowCount(); $index++) {
                    //row
                    $row = $oldContent->getRow($index);

                    $newRow = new UniversalFilterTableContentRow();
                    $newRow->defineValue($finalid, $this->doCalculate($row->getGroupedValue($sourceColumnId)));
                    
                    $newContent->addRow($newRow);
                }
                
            }
        }else{
            //multiple columns - not grouped
            $newRow = new UniversalFilterTableContentRow();

            for ($index = 0; $index < $this->header1->getColumnCount(); $index++) {
                $columnId = $this->header1->getColumnIdByIndex($index);
                $columnInfo = $this->header1->getColumnInformationById($columnId);
                
                $finalid = $this->header->getColumnIdByIndex($index);
                
                $values = $this->convertColumnToArray($oldContent, $columnId);
                
                $newRow->defineValue($finalid, $this->doCalculate($values));
            }
            $newContent->addRow($newRow);
        }
        
        $oldContent->tryDestroyTable();
        
        return $newContent;
    }
    
    private function doCalculate(array $values){
        if($this->errorIfNoItems()){
            if(count($values)==0){
                throw new Exception("This aggregator can not be applied to an empty column.");
            }
        }
        return $this->calculateValue($values);
    }
    
    
    public function initExpression(UniversalFilterNode $filter, Environment $topenv, IInterpreter $interpreter) {
        parent::initExpression($filter, $topenv, $interpreter);
        $this->makeAllColumnsHeader();
    }
    
    public function evaluateAsExpression() {
        return $this->callForAllColumns();
    }
    
    public function errorIfNoItems(){
        return false;
    }
}

?>
