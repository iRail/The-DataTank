<?php
/**
 * This file contains the abstact top class for all aggregators
 *
 * @package The-Datatank/universalfilter
 * @copyright (C) 2012 We Open Data
 * @license AGPLv3
 * @author Jeroen Penninck
 */
abstract class AggregatorFunctionExecuter extends ExpressionNodeExecuter {
    
    private $filter;
    
    private $header;
    
    private $executer1;
    
    private $header1;
    
    private $singleColumnSingleRow;
    
    /**
     * Call this method in the initExpression if the aggregator works on "arrays"
     */
    public function makeAllColumnsHeader(){
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
    
    /**
     * Call this method in the init IF this aggregator converts a complete table in a single field.
     */
    public function makeSingleColumnHeader(){
        $name="";
        if($this->header1->isSingleColumnByConstruction()){
            $columnId = $this->header1->getColumnId();
            $columnInfo = $this->header1->getColumnInformationById($columnId);
            
            $name=$columnInfo->getName();
        }else{
            $name="_multiple_columns_";
        }
        
        $this->header = new UniversalFilterTableHeader(array($this->getName($name)), true, true);
    }
    
    public function initExpression(UniversalFilterNode $filter, Environment $topenv, IInterpreter $interpreter){
        $this->filter = $filter;
        
        $this->executer1 = $interpreter->findExecuterFor($this->filter->getColumn());
        
        $this->executer1->initExpression($this->filter->getColumn(), $topenv, $interpreter);
        
        $this->header1 = $this->executer1->getExpressionHeader();
        
        
    }
    
    public function getExpressionHeader(){
        return $this->header;
    }
    
    public function callForAllColumns(){
        $oldContent = $this->executer1->evaluateAsExpression();
        $newContent = new UniversalFilterTableContent();
        
        echo "XXX";
        
        if($this->header1->isSingleColumnByConstruction()){
            $sourceColumnId = $this->header1->getColumnId();
            $finalid = $this->header->getColumnId();
            
            if($this->singleColumnSingleRow){
                //single column - not grouped
                $values=$this->convertColumnToArray($oldContent, $sourceColumnId);
                
                $row = new UniversalFilterTableContentRow();
                $row->defineValue($finalid, $this->calculateValue($values));
                
                $newContent->addRow($row);
            }else{
                echo "AAAAAAAAAAAAAAAAAAAAAAAA";
                //single column - grouped
                for ($index = 0; $index < $oldContent->getRowCount(); $index++) {
                    //row
                    $row = $newContent->getRow($index);

                    $newRow = new UniversalFilterTableContentRow();
                    $newRow->defineValue($finalid, $this->calculateValue($row->getGroupedValue($sourceColumnId)));
                    
                    $newContent->addRow($newRow);
                }
                
            }
        }else{
            echo "BBBBBBBBBBBBBBBBBBBBB";
            //multiple columns - not grouped
            $newRow = new UniversalFilterTableContentRow();

            for ($index = 0; $index < $this->header1->getColumnCount(); $index++) {
                $columnId = $this->header1->getColumnIdByIndex($index);
                $columnInfo = $this->header1->getColumnInformationById($columnId);
                
                $finalid = $this->header->getColumnIdByIndex($index);
                
                $values = $this->convertColumnToArray($oldContent, $columnId);
                
                $newRow->defineValue($finalid, $this->calculateValue($values));
            }
            $newContent->addRow($newRow);
        }
        return $newContent;
    }
    
    /**
     * Call this method in the init IF this aggregator converts a complete table in a single field.
     */
    public function callSingleColumn(){
        $oldContent = $this->executer1->evaluateAsExpression();
        $newContent = new UniversalFilterTableContent();
        
        $finalid = $this->header->getColumnId();
        
        $newRow = new UniversalFilterTableContentRow();
        $newRow->defineValue($finalid, $this->calculateValueForTable($oldContent));
        
        $newContent->addRow($newRow);
        
        return $newContent;
    }
    
    public function convertColumnToArray(UniversalFilterTableContent $content, $columnId){
        $arr = array();
        for ($index = 0; $index < $content->getRowCount(); $index++) {
            array_push($arr, $content->getRow($index)->getCellValue($columnId));
        }
        return $arr;
    }
    
    public function evaluateAsExpression() {
    }
    
    public function getName($name){
        return $name;
    }
    
    public function calculateValue(array $values){
        return 0;
    }
    
    public function calculateValueForTable(UniversalFilterTableContent $content){
        return 0;
    }
    
    public function keepFullInfo(){
        return true;
    }
}

?>
