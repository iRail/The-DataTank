<?php

class SystemCheck extends InstallController {
    
    public function index() {
        $tests = array();
        $extensions = get_loaded_extensions();
        
        // PHP version
        if(version_compare(PHP_VERSION, '5.3.1') >= 0)
            $tests["php_version"] = array("status"=>"passed", "value"=>PHP_VERSION);
        else
            $tests["php_version"] = array("status"=>"failed", "value"=>PHP_VERSION, "message"=>"php_version_low");   
        
        // MySQL version
        if(in_array("mysql", $extensions) || in_array("mysqli", $extensions)) {
            $version = reset(explode(".", mysql_get_server_info()));
            if($version >= 5)
                $tests["mysql_version"] = array("status"=>"passed", "value"=>mysql_get_server_info());
            else
                $tests["mysql_version"] = array("status"=>"failed", "value"=>mysql_get_server_info(), "message"=>"mysql_version_low");
        }
        //SQLite
        elseif(in_array("SQLite", $extensions)) {
            $version = reset(explode(".", sqlite_libversion()));
            if($version >= 3)
                $tests["sqlite_version"] = array("status"=>"passed", "value"=>sqlite_libversion());
            else
                $tests["sqlite_version"] = array("status"=>"failed", "value"=>sqlite_libversion(), "message"=>"sqlite_version_low");
        }
        //PostgreSQL
        elseif(in_array("pgsql", $extensions)) {
            $tests["postgresql_version"] = array("status"=>"warning", "value"=>"Unable to test", "message"=>"postgresql_version_check");
        }
            
        $data["tests"] = $tests;
        $this->view("system", $data);
    }
    
}