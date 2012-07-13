<?php

/**
 * This class can convert a table (as used by the interpreter) to a php-object
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
        for ($index = 0; $index < $table->getHeader()->getColumnCount(); $index++) {
            $id = $table->getHeader()->getColumnIdByIndex($index);
            $name = $table->getHeader()->getColumnNameById($id);
            foreach($table->getContent()->getRows() as $rindex => $row){
                $newRows[$rindex][$name] = $row->getValue($id);
            }
        }
        
        return $newRows;
    }
}

?>
