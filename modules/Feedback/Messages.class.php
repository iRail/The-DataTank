<?php
/* Copyright (C) 2011 by iRail vzw/asbl *
 * Author: Werner Laurensse <el.lauwer aŧ gmail.com>
 * License: AGPLv3
 */

include_once("modules/AMethod.php");
include_once("TDT.class.php");

class Liveboard extends AMethod {
    private $lang;
    private $system;
    private $time;
    private $direction;

    public function __construct() {
        // We need to be able to hande any combination of parameters so we do 
        // not call the constructor.
        //parent::__construct("Message");
    }

    public static function getDoc(){
        echo "TODO"; //TODO add doc
    }

    public function getData(){
	    /* Connect to mysql database */
	    $link = mysqli_connect(
	        'localhost',              /* The host to connect to */
	        Config::$MySQL_USER_NAME, /* The user to connect with the MySQL database */
	        Config::$MySQL_PASSWORD,   /* The password to use to connect with the db  */
	        Config::$MySQL_DATABASE);  /* The default database to query */

        if (!$link) {
            printf("Can't connect to MySQL Server. Errorcode: %s\n",
                mysqli_connect_error());
	       exit;
        }

        $pageUrl = TDT::get_page_url();

        $queryString = 'SELECT * FROM feedback_messages' . ' WHERE '
            ' WHERE url_request = ' . $pageUrl
        //echo "queryString is ". $queryString;
	  
        if ($result = mysqli_query($link, $queryString)) {
            /* Fetch the results of the query */
	        $this->queryResults = new QueryResults();

            while( $row = mysqli_fetch_assoc($result) ) {
                foreach($row as $key => $value) {
                    $this->queryResult->result[$key] = $value;
                }
            }
	        /* Destroy the result set and free the memory used for it. */
	        mysqli_free_result($result);
        }

        /* Close the connection */
        mysqli_close($link);
     }

    public function call() {
        $this->getData();
        return $this->queryResults;
    }

    public function setParameter($name,$val) {

    }

    public function allowedPrintMethods() {

    }
    

    

