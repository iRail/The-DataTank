<?php

/**
 * Executes the DataGrouper "filter"
 * 
 * @package The-Datatank/universalfilter/interpreter/executers
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jeroen Penninck
 */
class DataGrouperExecuter extends BaseHashingFilterExecuter {
    
    private $header;
    private $oldHeader;
    
    private $executer;
    
    private $newColumns;
    
    public function initExpression(UniversalFilterNode $filter, Environment $topenv, IInterpreter $interpreter) {
        //get source environment
        $this->executer = $interpreter->findExecuterFor($filter->getSource());
        $this->executer->initExpression($filter->getSource(), $topenv, $interpreter);
        
        //get the columns to group
        $columnIdentifiers = $filter->getColumns();
        
        
        // make the new header
        $this->oldHeader = $this->executer->getExpressionHeader();
        $this->newColumns = array();
        
        
        
        for ($index = 0; $index < $this->oldHeader->getColumnCount(); $index++) {
            $oldColumnInfo = $this->oldHeader->getColumnInformationById($this->oldHeader->getColumnIdByIndex($index));
            
            $needToBeGrouped=true;
            for ($columnNameIndex = 0; $columnNameIndex < count($columnIdentifiers); $columnNameIndex++) {
                $columnIdentifier = $columnIdentifiers[$columnNameIndex]->getIdentifierString();
                if($oldColumnInfo->matchName(explode(".", $columnIdentifier))){
                    $needToBeGrouped=false;
                }
            }
            
            $newColumnInfo=null;
            if($needToBeGrouped){
                $newColumnInfo = $oldColumnInfo->cloneColumnGrouped();
            }else{
                $newColumnInfo = $oldColumnInfo->cloneColumnNewId();
            }
            array_push($this->newColumns, $newColumnInfo);
        }
        
        $this->header = new UniversalFilterTableHeader($this->newColumns, false, false);
        
        // new header is generated....
    }
    
    public function getExpressionHeader() {
        return $this->header;
    }
    
    
    public function evaluateAsExpression() {
        
        $sourcetablecontent = $this->executer->evaluateAsExpression();
        
        //
        // now Group!
        //
        
        $bigListOfGroups = new BigList();
        $bigGroupMap = new BigMap();//of hashkey => array of indices in the rows of the source table that match the description

        //loop through all rows and check if they are in the map
        for ($index = 0; $index < $sourcetablecontent->getRowCount(); $index++) {
            $oldRow = $sourcetablecontent->getRow($index);
            $hash = "";
             for ($cindex = 0; $cindex < $this->oldHeader->getColumnCount(); $cindex++) {
                 $oldId = $this->oldHeader->getColumnIdByIndex($cindex);
                 $newColumn = $this->newColumns[$cindex];
                 $newId = $newColumn->getId();
                 $isGrouped = $newColumn->isGrouped();
                 
                 if(!$isGrouped){
                     //add to hash
                     $hash.=$oldRow->getHashForField($oldId)."%";//% is separator
                 }
             }
             if(!$bigGroupMap->containsMapValue($hash)){
                 $bigGroupMap->setMapValue($hash, array());
                 $bigListOfGroups->addItem($hash);
             }
             
             // add the index of the row to the map
             $oldArray = $bigGroupMap->getMapValue($hash);
             array_push($oldArray, $index);
             $bigGroupMap->setMapValue($hash, $oldArray);
        }
        
        
        //
        // grouping done, now create the content
        //
        $newRows = new UniversalFilterTableContent();
        
        for ($index = 0; $index < $bigListOfGroups->getSize(); $index++) {// FOR ALL GROUPS
            $hash = $bigListOfGroups->getIndex($index);
            $group = $bigGroupMap->getMapValue($hash);
            
            $newRow = new UniversalFilterTableContentRow();
            $groupedColumnValues = array();
            
            foreach ($group as $groupIndex => $value) {// A ROW IN THE GROUP
                $oldRow = $sourcetablecontent->getRow($value);
                
                for ($cindex = 0; $cindex < $this->oldHeader->getColumnCount(); $cindex++) {//A COLUMN IN A ROW IN THE GROUP
                    $oldId = $this->oldHeader->getColumnIdByIndex($cindex);
                    $newColumn = $this->newColumns[$cindex];
                    $newId = $newColumn->getId();
                    $isGrouped = $newColumn->isGrouped();
                    
                    $value = $oldRow->getCellValue($oldId);
                    
                    if($isGrouped){
                        $arr=array();
                        
                        if(isset($groupedColumnValues[$newId])){
                            $arr = $groupedColumnValues[$newId];
                        }
                        
                        array_push($arr, $value);
                        $groupedColumnValues[$newId]=$arr;
                    }else{
                        //just set the value
                        $groupedColumnValues[$newId]=$value;
                    }
                }
                
            }
            
            // NOW CREATE THE ROW
            for ($cindex = 0; $cindex < $this->oldHeader->getColumnCount(); $cindex++) {//A COLUMN IN THE GROUP
                $newColumn = $this->newColumns[$cindex];
                $newId = $newColumn->getId();
                $isGrouped = $newColumn->isGrouped();

                if($isGrouped){
                    $newRow->defineGroupedValue($newId, $groupedColumnValues[$newId]);
                }else{
                    $newRow->defineValue($newId, $groupedColumnValues[$newId]);
                }
            }
            
            $newRows->addRow($newRow);
        }
        
        $sourcetablecontent->tryDestroyTable();
        
        return $newRows;
    }
}

?>
