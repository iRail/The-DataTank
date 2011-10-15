<?php
/**
 * Installation step: database create
 *
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jens Segers
 */

class DatabaseCreate extends InstallController {
    
    public function index() {
        if(isset($_POST["user"]) && isset($_POST["pass"])) {
            $this->createDatabase($_POST["user"], $_POST["pass"]);
        }
        else {
            // try installation with config credentials
            $this->createDatabase(Config::$DB_USER, Config::$DB_PASSWORD);
        }
    }
    
    private function createDatabase($user, $pass) {
        include_once(dirname(__FILE__)."/../../Config.class.php");
        include_once(dirname(__FILE__)."/../../includes/rb.php");
        
        try {
        	$db_config = explode(";", Config::$DB);
            $dbname = end($db_config);
            $pieces = explode("=", $dbname);
            
            // get database name
            if(isset($pieces) && $pieces[0] == "dbname") {
                $dbname = $pieces[1];
                $db = str_replace(";dbname=".$dbname,"",Config::$DB);
                
                R::setup($db, $user, $pass);
                
                $query = "CREATE DATABASE IF NOT EXISTS ".$dbname." DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci";
                R::exec($query);
                
                $data["status"] = "passed";
            }
            else {
                $data["status"] = "failed";
                $data["message"] = "database_no_database";
                
                $this->installer->nextStep(FALSE);
                $this->installer->previousStep("DatabaseCheck");
            }
            
            // show database create success page
            $this->view("database_create", $data);
            
        }
        catch(Exception $e) {
            $data["status"] = "failed";
            $data["message"] = $e->getMessage();
            
            $this->installer->nextStep(FALSE);
            $this->installer->previousStep("DatabaseCheck");
            
            $this->view("database_root", $data);
        }
    }
    
}