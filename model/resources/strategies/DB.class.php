<?php
/**
 * This handles a database related resource
 *
 * @package The-Datatank/model/resources/strategies
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */
include_once("model/resources/strategies/ATabularData.class.php");
include_once("model/resources/actions/DBForeignRelation.class.php");

class DB extends ATabularData{
    
    private $updateActions; //array with possible updateActions

    public function __construct(){
        $this->updateActions = array();
        $this->updateActions["db_foreign_relation"] = new DBForeignRelation();
    }
    

    public function onCall($package,$resource){
        /*
         * Here we'll extract all the db-related info and return an object for the RESTful call
         * As we're not using different tables per different type of database we'll use separate logic
         * per separate database. Perhaps this could also be implemented in a strategypattern.....
         */
        try{
            $results = DBQueries::getDBResource($package, $resource);

            $dbtype = $results["db_type"];
            $dbname = $results["db_name"];
            $dbtable = $results["db_table"];
            $dbport = $results["port"];
            $dbhost = $results["host"];
            $user = $results["db_user"];
            $passwrd = $results["db_password"];
            $id = $results["id"];
            $gen_res_id = $results["gen_res_id"];
            
            // get the columns from the columns table
            $allowed_columns = DBQueries::getPublishedColumns($gen_res_id);
            
            $dbcolumns = array();
            $PK = "";
            foreach($allowed_columns as $result){
                array_push($dbcolumns,$result["column_name"]);
                if($result["is_primary_key"] == 1){
                    $PK = $result["column_name"];
                }
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
                $resultobject = $this->createResultObjectFromRB($resultobject,$dbcolumns,$dbtable,$id,$dbhost,$PK);
            }elseif(strtolower($dbtype) == "sqlite"){
                //$dbtable is used as path to the sqlite file. 
                R::setup("sqlite:$dbtable",$user,$passwrd); //sqlite
                $resultobject = $this->createResultObjectFromRB($resultobject,$dbcolumns,$dbtable,$id,$dbhost,$PK);
            }elseif(strtolower($dbtype) == "postgresql"){
                R::setup("pgsql:host=$dbhost;dbname=$dbname",$user,$passwrd); //postgresql
                $resultobject = $this->createResultObjectFromRB($resultobject,$dbcolumns,$dbtable,$id,$dbhost,$PK);
            }else{
                // TODO: provide interfacing with other db's too.
                throw new DatabaseTDTException("The database you're trying to reach is not yet supported.");
            }   
            return $resultobject;
        }catch(Exception $ex){
            throw new InternalServerTDTException("Something went wrong while fetching the 
                      requested databaseresource: ".$ex->getMessage()." .");
        }
    }

    /**
     * Creates result FROM a resultset returned by a RedBean php query
     * Note: If similar functionality is found in other db-interfacing such as
     * NoSQL, this could be used as a general build-up method.
     */
    private function createResultObjectFromRB($resultobject,$dbcolumns,$dbtable,$id,$host,$PK){
        $columns = "*";
        if(sizeof($dbcolumns) > 0 && $dbcolumns[0] != ""){
            $columns = implode(",",$dbcolumns);  
            $columns = $columns . ", id ";
        }

        $results = R::getAll(
            "SELECT $columns FROM $dbtable"
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
            /* 
             * if a column is submitted as primary key, then we dont build up our objects
             * as a normal array, but as a hash. Using the PK as identifier.
             * Note: The PK must be unique, this responsibility lies with the data publisher,
             * the datatank cannot forsee if the PK is unique in some table in some database.
             * Thus, when returning a resulting object, we look if the key alrdy exists in the hash, if so,
             * we choose not to override it.
             */
            if($PK == ""){
                array_push($arrayOfRowObjects,$rowobject);   
            }else{
                if(!isset($arrayOfRowObjects[$rowobject->$PK])){
                    $arrayOfRowObjects[$rowobject->$PK] = $rowobject;
                }
            }
        }
        $resultobject->object=$arrayOfRowObjects;
        return $resultobject;
    }

    private function createForeignRelationURLs($id,$host){
        $urls = array();
        
        $results = DBQueries::getForeignRelations($id);
        foreach($results as $result){
            $urls[ $result["keyname"] ] = Config::$HOSTNAME."".$result["package_name"]."/".$result["resource_name"]
                ."/object/?filterBy=id&filterValue=";
            
        }
        return $urls;
    }

    public function onDelete($package,$resource){
        DBQueries::deleteForeignRelation($package, $resource);
        DBQueries::deleteDBResource($package, $resource);
    }

    public function onAdd($package_id, $resource_id,$content){
        $this->evaluateDBResource($resource_id,$content);
        parent::evaluateColumns($content["columns"],$content["PK"],$resource_id);
    }
    

    private function evaluateDBResource($resource_id,$put_vars){
        DBQueries::storeDBResource($resource_id, $put_vars["dbtype"], $put_vars["dbname"], $put_vars["dbtable"], $put_vars["host"], $put_vars["port"], $put_vars["user"], $put_vars["password"]);
    }

    public function onUpdate($package,$resource,$content){
        if(isset($content["update_type"]) && 
           isset($this->updateActions[$content["update_type"]])){
                $updateAction = $this->updateActions[$content["update_type"]];
                $updateAction->update($package,$resource,$content);
        }else{
            throw new ResourceUpdateTDTException ("update type hasn't been specified or isn't applicable for the given package and resource: $package/$resource");
        }
    }
}
?>
