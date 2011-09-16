<?php
/**
 * Installation step: database check
 *
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jens Segers
 */

class DatabaseCheck extends InstallController {
    
    public function index() {
        include_once(dirname(__FILE__)."/../../Config.class.php");
        include_once(dirname(__FILE__)."/../../includes/rb.php");
        
        $data["credentials"]["DB"] = Config::$DB;
        $data["credentials"]["DB_USER"] = Config::$DB_USER;
        $data["credentials"]["DB_PASSWORD"] = ""; // don't output real password
        
        for($i=0; $i<strlen(Config::$DB_PASSWORD); $i++)
            $data["credentials"]["DB_PASSWORD"] .= "*";
        
        try {
            // try a simple query to test redbean's connection
            R::setup(Config::$DB, Config::$DB_USER, Config::$DB_PASSWORD);
            R::exec("SELECT 'hello'");
            
            $data["status"] = "passed";
        }
        catch(Exception $e) {
            $data["status"] = "failed";
            $data["message"] = $e->getMessage();
            
            // don't allow next step on error
            $this->installer->nextStep(FALSE);
        }
        
        $this->view("database_credentials", $data);
    }
    
}