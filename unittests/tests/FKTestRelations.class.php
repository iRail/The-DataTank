<?php
/**
 *
 * Test foreign keys
 *
 * Copyright (C) 2011 by iRail vzw/asbl 
 * Author: Jens Segers
 * License: AGPLv3
 */

include_once(dirname(__FILE__)."/simpletest/autorun.php");
include_once(dirname(__FILE__)."/TDTUnitTest.class.php");

class FKTestRelations extends TDTUnitTest{

    /*
     * Check if all relations have a valid resource
     */
    function testRelations(){
        $this->assertTrue($this->checkFK("foreign_relation", "main_object_id", "resource", "id"));
        $this->assertTrue($this->checkFK("foreign_relation", "foreign_object_id", "resource", "id"));
    }
    
    /*
     * A helper function to test the existance of foreign key relations
     */
    private function checkFK($mainTable, $mainColumn, $foreignTable, $foreignColumn) {
        $results = R::getAll("SELECT $foreignTable.$foreignColumn FROM $mainTable LEFT JOIN $foreignTable ON $mainTable.$mainColumn=$foreignTable.$foreignColumn GROUP BY $foreignTable.$foreignColumn");
        foreach($results as $result) {
            if(is_null($result[$foreignColumn]))
                return FALSE;
        }
        return TRUE;
    }
}

?>