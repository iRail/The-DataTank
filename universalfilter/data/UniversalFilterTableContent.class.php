<?php
/**
 * The content of the universal representation of a table
 *
 * @package The-Datatank/universalfilter/data
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jeroen Penninck
 */
class UniversalFilterTableContent {
    private $rows;
    
    public function __construct(array $rows) {
        $this->rows=$rows;
    }
    
    /**
     * return array of Rows
     */
    public function getRows(){
        return $this->rows;
    }
    
    
    /**
     * throws an exception if not a Column
     */
    public function getValue($name, $index){
        $rows=$this->getRows();
        return $rows[$index]->getValue($name);
    }
    
    /**
     * throws an exception if not a Cell
     */
    public function getCellValue($name){
        return $this->getValue($name, 0);
    }
    
    /**
     * Size of table (rows
     */
    public function getRowCount(){
        return Count($this->getRows());
    }
}

?>
