<?php

/**
 * This class can convert a table (as used by the interpreter) to a php-object
 *
 * @package The-Datatank/universalfilter/tablemanager/tools
 * @copyright (C) 2012 We Open Data
 * @license AGPLv3
 * @author Jeroen Penninck
 */
class TableToPhpObjectConverter {
    public function getPhpObjectForTable(UniversalFilterTable $table){
        $newRows = array();
        
        //initialize rows
        for ($index = 0; $index < $table->getContent()->getRowCount(); $index++) {
            $row = $table->getContent()->getRow($index);
            
            array_push($newRows, array());
        }
        
        //loop all columns
        for ($index = 0; $index < $table->getHeader()->getColumnCount(); $index++) {
            $id = $table->getHeader()->getColumnIdByIndex($index);
            $name = $table->getHeader()->getColumnNameById($id);
            if(!$table->getHeader()->getColumnInformationById($id)->isGrouped()){
                for ($rindex = 0; $rindex < $table->getContent()->getRowCount(); $rindex++) {
                    $row = $table->getContent()->getRow($rindex);
                    $newRows[$rindex][$name] = $row->getCellValue($id);
                }
            }
        }
        
        return $newRows;
    }
}

?>
