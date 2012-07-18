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
    private $size;
    
    public static $IDCOUNT=0;
    
    public function __construct() {
        $this->rows=new BigList();
        $this->size=0;
    }
    
    /**
     * Get the row on a certain index
     * @param int $index
     * @return UniversalFilterTableContentRow
     */
    public function getRow($index){
        if($index<$this->size){
            return $this->rows->getIndex($index);
        }else{throw new Exception("UniversalFilterTableContent: getRow: Index out of bounds");}//should not happen
    }
    
    /**
     * Sets the row on a cetain index
     * @param int $index
     * @param UniversalFilterTableContentRow $row 
     */
    public function setRow($index, $row){
        if($index<$this->size){
            $this->rows->setIndex($index, $row);
        }else{throw new Exception("UniversalFilterTableContent: setRow: Index out of bounds");}//should not happen
    }
    
    /**
     * Adds a row to this table
     * @param UniversalFilterTableContentRow $row 
     */
    public function addRow($row){
        $this->size++;
        $this->rows->addItem($row);
    }
    
    
    /**
     * Get a value of a column in a row
     * @param string $name
     * @param int $index
     */
    public function getValue($name, $index){
        return $this->getRow($index)->getCellValue($name);
    }
    
    /**
     * Get the value of a column in the first row
     * @param string $name
     * @return string
     */
    public function getCellValue($name){
        return $this->getValue($name, 0);
    }
    
    /**
     * Get the size of the table
     */
    public function getRowCount(){
        return $this->size;
    }
}

?>
