<?php

/**
 * Default implementation of IUniversalFilterTableHeader
 *
 * @author Jeroen
 */
class UniversalFilterTableHeader {
    private $columnNames;
    private $tableLinks;
    
    private $isSingleRowByConstruction;
    private $isSingleColumnByConstruction;
    
    public function __construct($columnNames, $tableLinks, $isSingleRowByConstruction, $isSingleColumnByConstruction) {
        $this->columnNames=$columnNames;
        $this->tableLinks=$tableLinks;
        $this->isSingleRowByConstruction=$isSingleRowByConstruction;
        $this->isSingleColumnByConstruction=$isSingleColumnByConstruction;
    }
    
    /**
     * return an array of columnnames
     */
    public function getColumnNames() {
        return $this->columnNames;
    }
    
    /**
     * return true if the column contains id's
     *    of one (or multiple) rows in another table
     */
    public function isLinkedColumn($columnName) {
        return isset ($tableLinks[$columnName]);
    }
    
    /**
     * returns the table where the column links to.
     */
    public function getLinkedTable($columnName) {
        return $tableLinks[$columnName]["table"];
    }
    
    /**
     * returns the field in the table which contains the key where is linked to.
     */
    public function getLinkedTableKey($columnName) {
        return $tableLinks[$columnName]["key"];
    }
    
    /**
     * returns if this table is constucted that way only one row can exist (e.g. after FIRST() or AVG() )
     */
    public function isSingleRowByConstruction() {
        return $this->isSingleRowByConstruction;
    }
    
    public function setIsSingleRowByConstruction($value) {
        $this->isSingleRowByConstruction=$value;
    }
    
    /**
     * return if this table is constructed that way only one cell can exist
     */
    public function isSingleCellByConstruction() {
        return $this->isSingleColumnByConstruction() && $this->isSingleRowByConstruction();
    }
    
    public function getColumnName(){
        $columnNames=$this->getColumnNames();
        if(!$this->isSingleColumnByConstruction()){
            throw new Exception("Not a single column.");
        }
        return $columnNames[0];
    }
    
    /**
     * return if this table is constructed that way only one column can exist (e.g. by a columnselector)
     */
    public function isSingleColumnByConstruction() {
        return $this->isSingleColumnByConstruction;
    }
    
    /**
     *
     */
    public function checkCell(){
        if(!$this->isSingleCellByConstruction()){
            throw new Exception("Not a single value");
        }
    }
    
    public function cloneHeader(){
        return new UniversalFilterTableHeader(
                $this->columnNames,
                $this->tableLinks,
                $this->isSingleRowByConstruction,
                $this->isSingleColumnByConstruction);
    }
}

?>
