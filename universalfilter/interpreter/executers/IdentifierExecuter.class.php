<?php

/**
 * Executes an Identifier
 *
 * format: 
 * - package.package.resource
 * - package.package.resource.name_of_column
 * - alias.name_of_column
 *
 * @package The-Datatank/universalfilter/interpreter/executers
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jeroen Penninck
 */
class IdentifierExecuter extends UniversalFilterNodeExecuter {

    private $filter;
    private $interpreter;
    private $topenv;
    
    private $header;
    
    private $singlevaluecolumnheader;
    private $singlevalueindex;
    
    private $isColumn;
    private $isNewTable;
    
    public function initExpression(UniversalFilterNode $filter, Environment $topenv, IInterpreter $interpreter) {
        $this->filter = $filter;
        $this->interpreter = $interpreter;
        $this->topenv = $topenv;
        
        $this->isColumn = true;
        $this->isNewTable = false;
        $this->header = $this->getColumnDataHeader($topenv, $this->filter->getIdentifierString());
        
        if($this->header==null){
            $this->isNewTable = true;
            //no matching column/value found => !! load new table
            $tableName = $filter->getIdentifierString();
            $this->header = $interpreter->getTableManager()->getTableHeader($tableName);
        }else{
            if(!$this->isColumn){
                $this->singlevaluecolumnheader=$this->header->getColumnInformationById($this->header->getColumnId());
                $this->header = new UniversalFilterTableHeader(array($this->singlevaluecolumnheader->cloneColumnNewId()), true, true);
            }
        }
    }
    
    public function getExpressionHeader(){
        return $this->header;
    }
    
    public function evaluateAsExpression() {
        if(!$this->isColumn){
            if($this->isNewTable){
                $tableName = $this->filter->getIdentifierString();
                return $this->interpreter->getTableManager()->getTableContent($tableName);
            }else{
                $newRow = new UniversalFilterTableContentRow();
                $value = $this->topenv->getSingleValue($this->singlevalueindex)->copyValueTo($newRow, $this->singlevaluecolumnheader->getId(), $this->header->getColumnId());
                return new UniversalFilterTableHeader(array($newRow), true, true);
            }
        }else{
            return $this->getColumnDataContent($this->topenv->getTable(), $this->filter->getIdentifierString(), $this->header);
            
        }
    }
    
    /*
     * TOOL METHODS:
     */
    
    /**
     * Get a single column from the data (header)
     * @return UniversalFilterTableHeader
     */
    private function getColumnDataHeader(Environment $topenv, $fullid){
        if($fullid=="*"){
            //special case => current table
            return $topenv->getTable()->getHeader()->cloneHeader();
        }
        
        $originalheader = $topenv->getTable()->getHeader();
        $columnid = $originalheader->getColumnIdByName($fullid);
        
        if($columnid==null){
            $this->isColumn=false;//it's a new table OR a single value...
            
            for ($index = 0; $index < $topenv->getSingleValueCount(); $index++) {
                $columninfo = $topenv->getSingleValueHeader($index);
                
                $columnid = $columninfo->getColumnIdByName($fullid);
                if($columnid!=null){
                    $this->singlevalueindex = $index;
                    return new UniversalFilterTableHeader(array($columninfo), true, true);
                }
                
            }
            return null;//it's a new table
        }else{
            $newHeaderColumn=$originalheader->getColumnInformationById($columnid)->cloneColumnNewId();

            $columnHeader = new UniversalFilterTableHeader(array($newHeaderColumn), $originalheader->isSingleRowByConstruction(), true);

            return $columnHeader;
        }
    }
    
    /**
     * Get a column from the data (content)
     * @param UniversalFilterTableHeader $header
     * @return UniversalFilterTableContent
     */
    private function getColumnDataContent($table, $fullid, $header){//get a single column from the table
        if($fullid=="*"){
            //special case
            return $table->getContent();
        }
        
        $oldheader = $table->getHeader();
        $oldcolumnid = $oldheader->getColumnIdByName($fullid);
        
        $newcolumnid = $header->getColumnId();

        //copyFields
        //$oldcolumnid -> $newcolumnid
        
        $content = $table->getContent();
        
        $newContent= new UniversalFilterTableContent();
        $rows=array();
        for ($index = 0; $index < $content->getRowCount(); $index++) {
            $oldRow = $content->getRow($index);
            $newRow = new UniversalFilterTableContentRow();
            $oldRow->copyValueTo($newRow, $oldcolumnid, $newcolumnid);
            
            $newContent->addRow($newRow);
        }
        
        return $newContent;
    }
}

?>
