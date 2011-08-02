<?php
  /**
   * This file contains the Messages method needed for the feedback concept.
   * @package The-DataTank	
   * @copyright (C) 2011 by iRail vzw/asbl 
   * @author: Werner Laurensse <el.lauwer aŧ gmail.com>
   * @license: AGPLv3
   */

//include_once("MDB2.php");
include_once("resources/AResource.class.php");

/**
 * Class messages. Allows to push a feedback for a certain method.
 */
class Messages extends AResource {
    private $lang;
    private $system;
    private $time;
    private $direction;

    public function __construct() {
        // We need to be able to handle any combination of parameters so we do 
        // not call the constructor.
        //parent::__construct("Message");
    }

    public function getDoc() {
        return "TODO"; //TODO add doc
    }

    public function getData() {
	    /* Connect to database */
        $conn = MDB2::factory(Config::$DSN, Config::$DB_OPTIONS);

        $pageUrl = TDT::getPageUrl();

        $stmt = $conn->prepare('SELECT * FROM feedback_messages WHERE url_request = ?',
            array('text'));
        $result = $stmt->execute($pageUrl, MDB2_FETCHMODE_OBJECT);
        $this->queryResult = $result->fetchAll();
        vardump($this->queryResult);

        $conn->disconnect();
        //if ($result = mysqli_query($link, $queryString)) {
            //[> Fetch the results of the query <]
			//$this->queryResults = new QueryResults();

            //while( $row = mysqli_fetch_assoc($result) ) {
                //foreach($row as $key => $value) {
                    //$this->queryResult->result[$key] = $value;
                //}
            //}
			//[> Destroy the result set and free the memory used for it. <]
			//mysqli_free_result($result);
        //}

        //[> Close the connection <]
        //mysqli_close($link);
    }

    public function call() {
        $this->getData();
        return $this->queryResults;
    }

    public function setParameter($name,$val) {

    }

    public function getAllowedPrintMethods() {
        return array("php","xml","json","jsonp");
    }

    public function getRequiredParameters() {
        return array();
    }

    public function getParameters() {
        return array();
    }
}
