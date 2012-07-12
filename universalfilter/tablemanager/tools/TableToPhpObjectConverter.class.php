<?php

/**
 * TODO...
 *
 * @package The-Datatank/universalfilter/tablemanager/tools
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jeroen Penninck
 */
class TableToPhpObjectConverter {
    public function getPhpObjectForTable(UniversalFilterTable $table){
        $newRows = array();
        
        //initialize rows
        foreach($table->getContent()->getRows() as $row){
            array_push($newRows, array());
        }
        
        //loop all columns
        foreach($table->getHeader()->getColumnNames() as $index => $column){
            foreach($table->getContent()->getRows() as $index => $row){
                $newRows[$index][$column] = $row->getValue($column);
            }
        }
        return $newRows;
    }
}

?>
