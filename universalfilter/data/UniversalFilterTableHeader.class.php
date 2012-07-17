<?php

/**
 * The header of the universal representation of a table
 *
 * @package The-Datatank/universalfilter/data
 * @copyright (C) 2012 We Open Data
 * @license AGPLv3
 * @author Jeroen Penninck
 */
class UniversalFilterTableHeader {
    private $columns;//array of UniversalFilterTableHeaderColumnInfo
    
    private $isSingleRowByConstruction;
    private $isSingleColumnByConstruction;
    
    public function __construct($columns, $isSingleRowByConstruction, $isSingleColumnByConstruction) {//TODO [X] check
        $this->columns=$columns;
        $this->isSingleRowByConstruction=$isSingleRowByConstruction;
        $this->isSingleColumnByConstruction=$isSingleColumnByConstruction;
    }
    
    /**
     * Rename this table
     */
    public function renameAlias($newname){
        throw new Exception("TODO [header-renameAlias]");
    }
    
    /**
     * Gets the columnId for a given name
     * @return string
     */
    public function getColumnIdByName($columnName) {
        $columnNameParts = explode(".", $columnName);
        $found=false;
        $id=null;
        foreach($this->columns as $column){
            if($column->matchName($columnNameParts)){
                if(!$found){
                    $found=true;
                    $id = $column->getId();
                }else{
                    throw new Exception("That identifier is not unique \"".$columnName."\"");
                }
            }
        }
        return $id;
    }
    
    /**
     * Gets the columnName for a given id
     */
    public function getColumnNameById($id) {
        return $this->getColumnInformationById($id)->getName();
    }
    
    /**
     * returns the number of columns
     */
    public function getColumnCount() {
        return count($this->columns);
    }
    
    /**
     * get a certain column id
     */
    public function getColumnIdByIndex($index) {
        return $this->columns[$index]->getId();
    }
    
    /**
     * get columnInformation
     * @return UniversalFilterTableHeaderColumnInfo
     */
    public function getColumnInformationById($id){
        foreach($this->columns as $column){
            if($column->getId()==$id){
                return $column;
            }
        }
        var_dump($id);
        throw new Exception("ColumnInformation not found for id: \"".$id."\"");
    }
    
    /**
     * returns if this table is constucted that way only one row can exist (e.g. after FIRST() or AVG() )
     */
    public function isSingleRowByConstruction() {
        return $this->isSingleRowByConstruction;
    }
    
    /**
     * sets the isSingleRowByConstruction value
     */
    public function setIsSingleRowByConstruction($value) {
        $this->isSingleRowByConstruction=$value;
    }
    
    /**
     * return if this table is constructed that way only one column can exist (e.g. by a columnselector)
     */
    public function isSingleColumnByConstruction() {
        return $this->isSingleColumnByConstruction;
    }
    
    /**
     * return if this table is constructed that way only one cell can exist
     */
    public function isSingleCellByConstruction() {
        return $this->isSingleColumnByConstruction() && $this->isSingleRowByConstruction();
    }
    
    /**
     * returns the only columnId (if a column)
     */
    public function getColumnId(){
        if(!$this->isSingleColumnByConstruction()){
            throw new Exception("Not a single column.");
        }
        return $this->getColumnIdByIndex(0);
    }
    
    /**
     * throws an exception if this is not a cell
     */
    public function checkCell(){
        if(!$this->isSingleCellByConstruction()){
            throw new Exception("Not a single value");
        }
    }
    
    /**
     * Clones this header...
     * Only usefull if you rename the table afterwards or if you set singleRowByConstruction.
     * 
     * @return UniversalFilterTableHeader 
     */
    public function cloneHeader(){
        return new UniversalFilterTableHeader(
                $this->columns,
                $this->isSingleRowByConstruction,
                $this->isSingleColumnByConstruction);
    }
}

?>
