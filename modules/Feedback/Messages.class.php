<?php
  /**
   * This file contains the Messages method needed for the feedback concept.
   * @package The-DataTank	
   * @copyright (C) 2011 by iRail vzw/asbl 
   * @author: Werner Laurensse <el.lauwer aŧ gmail.com>
   * @license: AGPLv3
   */

//include_once("MDB2.php");
include_once('rb.php');
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

    public static function getDoc() {
        return "TODO"; //TODO add doc
    }

    public function getData() {
        R::setup(Config::$DB, Config::$DB_USER, Config::$DB_PASSWORD);

        $self->queryResult = R::find(
            'feedback_messages',
            'url_request = :url_request',
            array(':url_request' => TDT::getPageUrl())
        ); 
    }

    public function call() {
        $this->getData();
        return $this->queryResults;
    }

    public function setParameter($name,$val) {

    }

    public static function getAllowedPrintMethods() {
        return array("php","xml","json","jsonp");
    }

    public static function getRequiredParameters() {
        return array();
    }

    public static function getParameters() {
        return array();
    }
}
