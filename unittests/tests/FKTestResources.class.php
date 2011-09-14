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

class FKTestPackage extends TDTUnitTest{

    /*
     * Check all package id's
     */
    function testPackage(){
        $this->assertTrue($this->checkFK("resource", "package_id", "package", "id"));
    }
    
    /*
     * Check all resource id's
     */
    function testResource() {
        $this->assertTrue($this->checkFK("generic_resource", "resource_id", "resource", "id"));
        $this->assertTrue($this->checkFK("remote_resource", "resource_id", "resource", "id"));
    }
    
    /*
     * Check all generic resource id's
     */
    function testGeneric() {
        $this->assertTrue($this->checkFK("generic_resource_csv", "gen_resource_id", "generic_resource", "id"));
        $this->assertTrue($this->checkFK("generic_resource_db", "gen_resource_id", "generic_resource", "id"));
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