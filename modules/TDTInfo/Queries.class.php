<?php 
/**
 * This class is returns the number of queries/errors made on/in the API/methods per day.
 *
 * @package The-Datatank/modules/TDTInfo
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert   <pieter@iRail.be>
 * @author Jan Vansteenlandt <jan@iRail.be>
 */

class Queries extends AResource{

    private $module; // must be set! Contains the value of the module that needs to be analysed.
    private $resource; // if set only look at certain data from a certain method within the given module.
    private $errors = ""; // if set, get data from errors table. ( not set or true )
    private $queryResults;

    public function getParameters(){
	return array("module" => "Name of a module that needs to be analysed, must be set !",
		     "resource" => "Name of a resource within the given module, is not required.",
		     "error" => "If set then the analysis will get it's data from the error table if not from the request table."
	);
    }

    public function getRequiredParameters(){
	return array("module");
    }

    public function setParameter($key,$val){
	if($key == "module"){
	    $this->module = $val;
	}elseif($key == "resource"){
	    $this->resource = $val;
	}elseif($key == "error"){
	    $this->errors = $val;
	}
    }

    private function getData(){

	/* Connect to mysql database */
	$link = mysqli_connect(
	    'localhost',                    /* The host to connect to */
	    Config::$MySQL_USER_NAME,       /* The user to connect with the MySQL database */
	    Config::$MySQL_PASSWORD,        /* The password to use to connect with the db  */
	    Config::$MySQL_DATABASE);                     /* The default database to query */

	if (!$link) { //Who wrote this piece of code? Please throw TDTExceptions ffs
	    printf("Can't connect to MySQL Server. Errorcode: %s\n", mysqli_connect_error());
	    exit;
	}
    $conn = MDB2::factory(Config::$DSN, Config::$DB_OPTIONS);

	/* Send a query to the server */
	if($this->errors == ""){
	    $databasetable = "requests";
	}else{
	    $databasetable = "errors";
	}	  

    $queryString = 'select count(1) as amount, time from ? where url_request regexp \'?/?\' group by from_unixtime(time, ,\'%D %M %Y\')';
    $stmt = $conn->prepare($queryString, array('text', 'text', 'text'));
    $this->resultQuery->result = $stmt->execute(array(databasetable, $this->module,
        $this->resource,
        MDB2_FETCHMODE_OBJECT)); 

    $conn->disconnect();
    
	//$queryString = 'SELECT count(1) as amount, time FROM ' . $databasetable . ' WHERE url_request REGEXP \''. $this->module . '/'.$this->resource .  '\' group by from_unixtime(time,\'%D %M %Y\')'; //echo "queryString is ". $queryString;
	  
	//if ($result = mysqli_query($link,$queryString)) {

		//[> Fetch the results of the query <]
		//$this->queryResults = new StdClass();
		   
		//while($row = mysqli_fetch_assoc($result)) {
		//$amount = $row['amount'];
		//$time  = $row['time'];
		//$this->queryResults->result[$time] = $amount;
		//}

		//[> Destroy the result set and free the memory used for it. <]
		//mysqli_free_result($result);
	//}

	//[> Close the connection <]
	//mysqli_close($link);
    }

    public function call(){
	$this->getData();
	return $this->queryResults;
    }

    public function getAllowedPrintMethods(){
	return array("json","xml", "jsonp", "php", "html");
    }

    public function getDoc(){
	return "Lists the number of queries(requests/errors) to this datatank instance per day";
    }
}
?>
