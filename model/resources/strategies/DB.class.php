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
include_once("model/DBQueries.class.php");
class DB extends ATabularData{

    private $NUMBER_OF_ITEMS_PER_PAGE = 50;

    public function documentCreateRequiredParameters(){
        return array("db_type","host","db_name","db_table","port","db_user","db_password");
    }
    
    //We could specify extra filters here for DB resources
    public function documentReadRequiredParameters(){
        return array();
    }
    
    public function documentCreateParameters(){
        return array("db_type" => "The type of the database engine, i.e. MySQL,PostgreSQL,SQLite.",
                     "db_name" => "The name of the database of which a table is to be published.",
                     "db_table" => "The name of the databas table that's supposed to be published.",
                     "host" => "The host to connect to in order to get access to the database.",
                     "db_user" => "The user to log into the database.",
                     "db_password" => "The password to log into the database.",
                     "port" => "The port to connect to on the host in order to get access to the database.",
                     "columns" => "The columns to publish.",
                     "PK" => "The primary key for each row.");
    }
    
    public function documentReadParameters(){
        return array();
    }


    public function __construct(){
        /**
         * Add a foreign key relation update action
         */
        $this->updateActions[] = "foreign_relation";
    }
    
    public function readPaged($package,$resource,$page){
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
        
            $resultobject = array();
            if(strtolower($dbtype) == "mysql"){
                R::setup("mysql:host=$dbhost;dbname=$dbname",$user,$passwrd);
            }elseif(strtolower($dbtype) == "sqlite"){
                //$dbtable is used as path to the sqlite file. 
                R::setup("sqlite:$dbtable",$user,$passwrd); //sqlite
            }elseif(strtolower($dbtype) == "postgresql"){
                R::setup("pgsql:host=$dbhost;dbname=$dbname",$user,$passwrd); //postgresql
            }else{
                throw new DatabaseTDTException("The database you're trying to reach is not yet supported.");
            }   

             $upperbound = $page * $NUMBER_OF_ITEMS_PER_PAGE -1; // MySQL LIMIT starts with 0
             $lowerbound = $upperbound - $NUMBER_OF_ITEMS_PER_PAGE;

             $resultobject = $this->createPagedResultObjectFromRB($resultobject,$dbcolumns,$dbtable,$id,$dbhost,$PK,$resource,$lowerbound,$upperbound);
            return $resultobject;
        }catch(Exception $ex){
            throw new InternalServerTDTException("Something went wrong while fetching the 
                      requested databaseresource: ".$ex->getMessage()." .");
        }
    }

    public function readNonPaged($package,$resource){
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
        
            $resultobject = array();
            if(strtolower($dbtype) == "mysql"){
                R::setup("mysql:host=$dbhost;dbname=$dbname",$user,$passwrd);
            }elseif(strtolower($dbtype) == "sqlite"){
                //$dbtable is used as path to the sqlite file. 
                R::setup("sqlite:$dbtable",$user,$passwrd); //sqlite
            }elseif(strtolower($dbtype) == "postgresql"){
                R::setup("pgsql:host=$dbhost;dbname=$dbname",$user,$passwrd); //postgresql
            }else{
                // TODO: provide interfacing with other db's too.
                throw new DatabaseTDTException("The database you're trying to reach is not yet supported.");
            }   
            $resultobject = $this->createResultObjectFromRB($resultobject,$dbcolumns,$dbtable,$id,$dbhost,$PK,$resource);
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
    private function createResultObjectFromRB($resultobject,$dbcolumns,$dbtable,$id,$host,$PK,$resource){
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
     
            foreach($result as $key => $value){
                $rowobject->$key = $value;
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

        return $arrayOfRowObjects;
    }

    /**
     * Perhaps we can merge the non paged and non paged creation of a resulting object
     * but for now the argumentlist is big enough, and two arguments are sometimes not used
     * in best practise a function does 1 single thing. After all the amount of duplicated code is minor.
     */
    private function createPagedResultObjectFromRB($resultobject,$dbcolumns,$dbtable,$id,$host,$PK,$resource,$lowerbound,$upperbound){
        $columns = "*";
        if(sizeof($dbcolumns) > 0 && $dbcolumns[0] != ""){
            $columns = implode(",",$dbcolumns);  
            $columns = $columns . ", id ";
        }

        $results = R::getAll(
            "SELECT $columns 
             FROM $dbtable
             LIMIT $lowerbound,$upperbound"
        );
        
        // create resulting object
        $arrayOfRowObjects = array();

        // foreach result check if they have an entry in the foreign relation table
        foreach($results as $result){
            $rowobject = new stdClass();
     
            foreach($result as $key => $value){
                $rowobject->$key = $value;
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

        return $arrayOfRowObjects;
    }

    public function onDelete($package,$resource){
        DBQueries::deleteDBResource($package, $resource);
    }

    public function onAdd($package_id, $resource_id){
        if(!isset($this->PK)){
            $this->PK = "";
        }
        $this->evaluateDBResource($resource_id);
        parent::evaluateColumns($this->columns,$this->PK,$resource_id);
    }
    

    private function evaluateDBResource($resource_id){
        DBQueries::storeDBResource($resource_id, $this->db_type, $this->db_name, 
                                   $this->db_table, $this->host, $this->port,
                                   $this->db_user , $this->db_password);
    }
}
?>
