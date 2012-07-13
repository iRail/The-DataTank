<?php

/**
 * An environment is passed to the filterexecuters while executing a query
 *
 * @package The-Datatank/universalfilter/interpreter
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jeroen Penninck
 */
class Environment {
    //
    // Identifier should be able to ask: 
    //  - what is the full table name of this alias?
    //  - can you give me the table with full name ... ?
    //  - give me the column with name ...
    //  - give me a cell with name ...
    //  
    //  => Aliases and TableManager and other Tables should be kept inside Environment
    //
    
    private $table=null;

    /**
     * Manage tables
     */
    
    /**
     * set the current table
     */
    public function setTable(UniversalFilterTable $table) {
        $this->table=$table;
    }
    
    /**
     * get the last added table
     */
    public function getTable(){
        return $this->table;
    }
    
    /**
     * Get a single column from the data (header)
     */
    public function getColumnDataHeader($fullid){
        $oldheader = $this->table->getHeader();
        $columnid = $oldheader->getColumnIdByName($fullid);
        
        if($columnid==null){
            throw new Exception("Column not found".$fullid.".");
        }
        
        $newHeaderColumn=$oldheader->getColumnInformationById($columnid)->cloneColumnNewId();

        $columnHeader = new UniversalFilterTableHeader(array($newHeaderColumn), $oldheader->isSingleRowByConstruction(), true);


        return $columnHeader;
    }
    
    /**
     * Get a column from the data (content)
     */
    public function getColumnDataContent($fullid, $header){//get a single column from the table
        $oldheader = $this->table->getHeader();
        $oldcolumnid = $oldheader->getColumnIdByName($fullid);
        
        $newcolumnid = $header->getColumnId();

        //copyFields
        //$oldcolumnid -> $newcolumnid
        
        $oldRows=$this->table->getContent()->getRows();
        $rows=array();
        foreach($oldRows as $index => $oldRow){
            $newRow=new UniversalFilterTableContentRow();
            $oldRow->copyValueTo($newRow, $oldcolumnid, $newcolumnid);
            $rows[$index] = $newRow;
        }

        $columnContent = new UniversalFilterTableContent($rows);
        return $columnContent;
    }

    /**
     * Clone Environment
     */
    public function newModifiableEnvironment(){
        $newEnv=new Environment();
        $newEnv->setTable($this->getTable());
        return $newEnv;
    }
}

?>
