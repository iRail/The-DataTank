<?php

 /**
   * This handles a database related resource
   *
   * @package The-Datatank/resources/strategies
   * @copyright (C) 2011 by iRail vzw/asbl
   * @license AGPLv3
   * @author Jan Vansteenlandt
   */
class DB extends AResourceStrategy{
    
    public function __construct(){
        
    }
    
    private function call($module,$resource){
        /*
         * Here we'll extract all the db-related info and return an object for the RESTful call
         * As we're not using different tables per different type of database we'll use separate logic
         * per separate database. Perhaps this could also be implemented in a strategypattern.....
         */
        R::setup(Config::$DB,Config::$DB_USER,Config::$DB_PASSWORD);
	$param = array(':module' => $module, ':resource' => $resource);
	$results = R::getAll(
	    "select db_name,db_table,host,port,type,columns from generic_resource_db,module, generic_resource 
             where module.module_name=:module and generic_resource_db.resource_name=:resource
             and module.id=generic_resource.module_id and generic_resource.id=generic_resource_db.resource_id",
	    $param
	);
        $dbtype = $results[0]["type"];
        $dbname = $results[0]["db_name"];
        $dbtable = $results[0]["db_table"];
        $dbport = $results[0]["port"];
        $dbhost = $results[0]["host"];
        $dbcolumns = $results[0]["columns"];
        
    }
  }
?>