<?php

/**
 * This class handles a database resource
 * The supported engines are entirely dependent on what engines our DBAL can handle
 * which currently is the doctrine project (http://www.doctrine-project.org/)
 *
 * @package The-Datatank/custom/strategies
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */
include_once("custom/strategies/ATabularData.class.php");
include_once(Config::$INSTALLDIR . Config::$SUBDIR.  "doctrine/Doctrine/Common/ClassLoader.php");
include_once("aspects/logging/BacklogLogger.class.php");

use Doctrine\Common\ClassLoader;

class DB extends ATabularData {


    private static $allowed_db_driver = array(    "mysql" => "pdo_mysql", 
                                                  "pgsql" => "pdo_pgsql",
                                                  "sqlite" => "pdo_sqlite",
                                                  "oci8"=> "oci8",
                                                  "sqlsrv"=> "pdo_sqlsrv"
    );

    public function __construct(){
        $this->parameters["columns"] = "An array that contains the name of the columns that are to be published, if an empty array is passed every column will be published. This array should be build as column_name => column_alias.";
        
    }
    

    /**
     * The parameters returned are required to make this strategy work.
     * @return array with parameter => documentation pairs
     */
    public function documentCreateRequiredParameters() {
        return array("db_type","location","db_table");
    }

    /**
     * The parameters ( array keys ) returned all of the parameters that can be used to create a strategy.
     * @return array with parameter => documentation pairs
     */
    public function documentCreateParameters() {
        $this->parameters["username"] = "The username to connect to the database with. This is required except for SQLite engines.";
        $this->parameters["password"] = "The password of the user to connect to the database. This is required except for SQLite engines.";
        $this->parameters["db_name"] = "The database name, all except sqlite needs to fill in this parameter.";
        $this->parameters["db_type"] = "The type of the database, current supported types are: " . implode(array_keys(DB::$allowed_db_driver),",");
        $this->parameters["db_table"] = "The database table of which some or all fields will be published.";
        $this->parameters["location"] = "The location of the database, for sqlite this will be the path towards the sqlite file, for all the other database types this will be the host on which the database is installed.";
        $this->parameters["port"] = "The port number to connect to. This is not relevant for sqlite files.";
        $this->parameters["PK"] = "The primary key of an entry. This must be the name of an existing column name in the tabular resource.";
        return $this->parameters;
    }

    /**
     * Returns an array with parameter => documentation pairs that can be used to read a CSV resource.
     * @return array with parameter => documentation pairs
     */
    public function documentReadParameters() {
        return array();
    }
    
    /**
     * Read a resource
     * @param $configObject The configuration object containing all of the parameters necessary to read the resource.
     * @param $package The package name of the resource 
     * @param $resource The resource name of the resource
     * @return $mixed An object created with fields of a CSV file.
     */
    public function read(&$configObject,$package,$resource){
        /*
         * First retrieve the values for the generic fields of the CSV logic
         * This is the uri to the file, and a parameter which states if the CSV file
         * has a header row or not.
         */
        parent::read($configObject,$package,$resource);

        $fields = implode(array_keys($configObject->columns),",");

        // prepare to get some of them data from the database!
        $sql = "SELECT $fields FROM $configObject->db_table";

        $classLoader = new ClassLoader('Doctrine', Config::$INSTALLDIR . Config::$SUBDIR.  "doctrine");
        $classLoader->register();
        $config = new \Doctrine\DBAL\Configuration();

        

        $connectionParams = $this->prepareConnectionParams($configObject);

        $conn = Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
        $stmt = $conn->query($sql);

        $table_columns = array_keys($configObject->columns);
        $aliases = $configObject->columns;

        $arrayOfRowObjects = array();
        while ($row = $stmt->fetch()) {
            $rowobject = new stdClass();
            $PK = $configObject->PK;

            // get the data out of the row and create an object out of it
            foreach($table_columns as $table_column){
                $key = $aliases[$table_column];
                $rowobject->$key = $row[$table_column];
            }

            /**
             * Add the object to the array of row objects
             */
            if ($PK == "") {
                array_push($arrayOfRowObjects, $rowobject);
            } else {
                if (!isset($arrayOfRowObjects[$rowobject->$PK]) && $rowobject->$PK != "") {
                    $arrayOfRowObjects[$rowobject->$PK] = $rowobject;
                }elseif(isset($arrayOfRowObjects[$rowobject->$PK])){
                    // this means the primary key wasn't unique !
                    BacklogLogger::addLog("DB", "Primary key ". $rowobject->$PK ." isn't unique.",
                                          $package,$resource);
                }else{
                    // this means the primary key field was empty, log the problem and continue 
                    BacklogLogger::addLog("DB", "Primary key is empty on line ". $line . ".", 
                                          $package,$resource);
                }
            }

        }
        return $arrayOfRowObjects;
    }

    /**
     * Prepares the connection parameters from the configObject, used in the read function
     */
    private function prepareConnectionParams($configObject){
        if(strtolower($configObject->db_type) == "sqlite"){
            return array(
                'path' => $configObject->location,
                'user' => $configObject->username,
                'password' => $configObject->password,
                'driver' => DB::$allowed_db_driver[$configObject->db_type]
            );
        }else{
            return array(
                'host' => $configObject->location,
                'user' => $configObject->username,
                'password' => $configObject->password,
                'driver' => DB::$allowed_db_driver[$configObject->db_type],
                'dbname' => $configObject->db_name,
                'port' => $configObject->port
            );
        }
    }

    protected function isValid($package_id,$generic_resource_id) {
        /**
         * Check if parameters for non sqlite engines are all passed, create the connection string
         * check if a connection can be made, check if the columns (if any are passed) are
         * existing ones in the database, if not get the columns from the datatable 
         */

        if(!isset($this->username)){
            $this->username = "";
        }
        
        if(!isset($this->password)){
            $this->password = "";
        }
        

        /**
         * Check if there is a ";" passed in the table parameter, if so give back an error
         */
        if(strpos($this->db_table,";") != FALSE){
            throw new ParameterTDTException("Your database table has a semi-colon in it, this is not allowed!");
        }
        
        /**
         * validate according to the db engine
         */
        $this->db_type = strtolower($this->db_type);
            
        if($this->db_type == "sqlite"){
            /*
             * Port and db_name are not necessary in this context
             */
            if(isset($this->port)){
                throw new ParameterTDTException("Port number isn't allowed when opening an SQLite database.");
            }elseif(isset($this->db_name)){
                throw new ParameterTDTException("The database name isn't applicable when opening an SQLite database.");
            }

            /**
             * Now we're going to check if the columns passed are present in the table.
             * 1) Prepare the connection
             * 2) Get the columnnames from the table
             * 3) Check if the passed columns are in the set of column names gotten from the table
             *    a. if no columns are passed, push all of the column names into the $this->columns array
             * 4) If the columns are all A-OK! then return true.
             * All this functionality has been put into functions.
             */
                 
            $table_columns = $this->getTableColumns();
            $this->validateColumns($table_columns);
            return true;
        }else{
            if(!isset($this->username)){
                throw new ParameterTDTException("username is required if you're opening up a table of a non SQLite database.");
            }

            if(!isset($this->password)){
                throw new ParameterTDTException("password is required if you're opening up a table of a non SQLite database.");
            }

            if(!isset($this->db_name)){
                throw new ParameterTDTException("db_name is required if you're opening up a table of a non SQLite database.");
            }
            
            /**
             * Now we're going to check if the columns passed are present in the table.
             * 1) Prepare the connection
             * 2) Get the columnnames from the table
             * 3) Check if the passed columns are in the set of column names gotten from the table
             *    a. if no columns are passed, push all of the column names into the $this->columns array
             * 4) If the columns are all A-OK! then return true.
             * All this functionality has been put into functions.
             */
            
            $table_columns = $this->getTableColumns();
            $this->validateColumns($table_columns);
            return true;
        }
            
    }

    /**
     * Check if the columns passed are in the table 
     * if no columns are passed, then fill up the $this->columns with the columns gotten from the table
     */
    private function validateColumns($table_columns){
        if(!isset($this->columns)){
            $this->columns = array();
            foreach($table_columns as $column){
                $this->columns[$column] = $column;
            }
        }else{
            $aliases =$this->columns;
            $this->columns = array();
            // make the columns as columnname => columnname
            // then in the second foreach put the aliases in the columns array (which technically is a hash)
            foreach($table_columns as $column){
                $this->columns[$column] = $column;
            }
            
            foreach($aliases as $column => $alias){
                if(array_key_exists($column,$this->columns)){
                    $this->columns[$column] = $alias;
                }else{
                    throw new ResourceAdditionTDTException("The column $column for which an alias ( $alias ) was given doesn't exist.");
                }
            }
        }
        return true;
    }

    /**
     * This function gets the names for the columns of a certain database table 
     */
    private function getTableColumns(){
        /**
         * Prepare the doctrine DBAL
         */
        $classLoader = new ClassLoader('Doctrine', Config::$INSTALLDIR . Config::$SUBDIR.  "doctrine");
        $classLoader->register();
        $config = new \Doctrine\DBAL\Configuration();

        /**
         * SQLite doesnt need password and username, yet they are required in the doctrine configuration object
         * We don't want to deal with null values, so lets fill them up with empty values
         */
        if(!isset($this->username)){
            $this->username = "";
        }
        
        if(!isset($this->password)){
            $this->password = "";
        }

        if(!isset($this->port)){
            $this->port = "";
        }
        
        
        $connectionParams = array();
        
        if($this->db_type == "sqlite"){
            $connectionParams = array(
                'path' => $this->location,
                'user' => $this->username,
                'password' => $this->password,
                'driver' => DB::$allowed_db_driver[$this->db_type]
            );
        }else{
            $connectionParams = array(
                'host' => $this->location,
                'user' => $this->username,
                'password' => $this->password,
                'driver' => DB::$allowed_db_driver[$this->db_type],
                'dbname' => $this->db_name,
                'port' => $this->port
            );
        }

        $conn = Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
        $table_columns = array();
        $sm = $conn->getSchemaManager();
        $columns = $sm->listTableColumns($this->db_table);
        foreach ($columns as $column) {
            array_push($table_columns,$column->getName());
        }
        return $table_columns;
    }
}
?>
