<?php
/**
 * A row in the content of the universal representation of a table
 *
 * @package The-Datatank/universalfilter/data
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jeroen Penninck
 */
class UniversalFilterTableContentRow {
    private $data;
    
    public function __construct() {
        $this->data=new stdClass();
    }
    
    public function defineValue($nameOfField, $value){
        if($nameOfField=="") throw new Exception("Not a valid fieldname...");// Can happen?
        $this->data->$nameOfField=$value;
    }
    
    public function getValue($nameOfField){//don't give all information
        if(isset($this->data->$nameOfField)){
            return $this->data->$nameOfField;
        }else{
            return null;
        }
    }
    
    public function copyValueTo(UniversalFilterTableContentRow $newRow, $oldField, $newField){
        $newRow->defineValue($newField, $this->data->$oldField);
    }
}
?>
