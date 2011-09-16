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
            $this->installer->nextStep(FALSE);
            $this->view("database_root");
        }
    }
    
    private function createDatabase($user, $pass) {
        include_once(dirname(__FILE__)."/../../Config.class.php");
        include_once(dirname(__FILE__)."/../../includes/rb.php");
       
        try {
            $dbname = end(explode(";", Config::$DB));
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
            }
            
        }
        catch(Exception $e) {
            $data["status"] = "failed";
            $data["message"] = $e->getMessage();
        }
        
        if($data["status"] == "failed") {
            // don't allow next step on error and set this step again as previous step
            $this->installer->nextStep(FALSE);
            $this->installer->previousStep("DatabaseCreate");
        }
        
        $this->view("database_create", $data);
    }
    
}