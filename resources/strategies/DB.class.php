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
    
    public function call($module,$resource){
        /*
         * Here we'll extract all the db-related info and return an object for the RESTful call
         * As we're not using different tables per different type of database we'll use separate logic
         * per separate database. Perhaps this could also be implemented in a strategypattern.....
         */
        try{
            
            R::setup(Config::$DB,Config::$DB_USER,Config::$DB_PASSWORD);
            $param = array(':module' => $module, ':resource' => $resource);
            $results = R::getAll(
                "select generic_resource_db.id,db_name,db_table,host,port,db_type,columns,db_user,db_password 
             from module,generic_resource_db,generic_resource 
             where module.module_name=:module and module.id=generic_resource.module_id 
             and generic_resource.resource_name=:resource 
             and generic_resource_db.resource_id=generic_resource.id",
                $param
            );
            $dbtype = $results[0]["db_type"];
            $dbname = $results[0]["db_name"];
            $dbtable = $results[0]["db_table"];
            $dbport = $results[0]["port"];
            $dbhost = $results[0]["host"];
            $user = $results[0]["db_user"];
            $passwrd = $results[0]["db_password"];
            $id = $results[0]["id"];
            
            // if no columns are passed along, by an "" value in the field "columns"
            // then we need to pass along an empty array for the $dbcolumns value
            if($results[0]["columns"] != ""){
                $dbcolumns = explode(";",$results[0]["columns"]);    
            }else{
                $dbcolumns = array();
            }

            /*
             * According to the type of db we're going to connect with the database and 
             * retrieve the correct fields. Since we're using redbean, we might as well use it
             * to retrieve some data when the host is supported by the redbean. The only reason 
             * why this could/should be changed is to provide functionality for older non-compatible
             * versions of mysql/sqlite/postgresql.
             */
        
            $resultobject = new stdClass();
            if(strtolower($dbtype) == "mysql"){
                R::setup("mysql:host=$dbhost;dbname=$dbname",$user,$passwrd);
                $resultobject = $this->createResultObjectFromRB($resultobject,$dbcolumns,$dbtable,$id,$dbhost);
            }elseif(strtolower($dbtype) == "sqlite"){
                //$dbtable is used as path to the sqlite file. 
                R::setup("sqlite:$dbtable",$user,$passwrd); //sqlite
                $resultobject = $this->createResultObjectFromRB($resultobject,$dbcolumns,$dbtable,$id,$dbhost);
            }elseif(strtolower($dbtype) == "postgresql"){
                R::setup("pgsql:host=$dbhost;dbname=$dbname",$user,$passwrd); //postgresql
                $resultobject = $this->createResultObjectFromRB($resultobject,$dbcolumns,$dbtable,$id,$dbhost);
            }else{
                // provide interfacing with other db's too.
                throw new DatabaseTDTException("The database you're trying to reach is not yet supported.");
            }   
            return $resultobject;
        }catch(Exception $ex){
            throw new InternalServerTDTException("Something went wrong while fetching the 
                      requested databaseresource: ".$ex->getMessage()." .");
        }
    }

    /**
     * Creates result from a resultset returned by a RedBean php query
     * Note: If similar functionality is found in other db-interfacing such as
     * NoSQL, this could be used as a general build-up method.
     */
    private function createResultObjectFromRB($resultobject,$dbcolumns,$dbtable,$id,$host){
        $columns = "*";
        if(sizeof($dbcolumns) > 0){
            $columns = implode(",",$dbcolumns);  
            $columns = $columns . ", id ";
        }

        $results = R::getAll(
            "select $columns from $dbtable"
        );

        // create resulting object
        $arrayOfRowObjects = array();

        // foreach result check if they have an entry in the foreign relation table
        foreach($results as $result){
            $rowobject = new stdClass();
            // create hash for every key that's a Foreign relation in the result.
            $foreignrelations = $this->createForeignRelationURLs($id,$host);

            foreach($result as $key => $value){
                if(array_key_exists($key,$foreignrelations)){
                    $rowobject->$key = $foreignrelations[$key].$value;
                }else{
                    $rowobject->$key = $value;
                }
            }
            array_push($arrayOfRowObjects,$rowobject);
        }
        $resultobject->object=$arrayOfRowObjects;
        return $resultobject;
    }

    private function createForeignRelationURLs($id,$host){
        $urls = array();
        $param = array();
        $param[":id"] = $id;
        
        $results = R::getAll(
            "select module.module_name as module_name, gen_res.resource_name as resource_name, 
             main_object_column_name as keyname
             from db_foreign_relation as for_rel,
             generic_resource_db as gen_res_db,
             generic_resource as gen_res,
             module
             where for_rel.main_object_id =:id and
                   for_rel.foreign_object_id=gen_res_db.id and
                   gen_res_db.resource_id=gen_res.id and
                   gen_res.module_id = module.id",
            $param
        );

        foreach($results as $result){
            $urls[ $result["keyname"] ] = $host."/".$result["module_name"]."/".$result["resource_name"]
                ."/object/?format=:format&filterBy=id&filterValue=";
            
        }
        
        return $urls;
    }
  }
?>
