<?php
  /**
   * This file contains the Messages method needed for the feedback concept.
   * @package The-DataTank/packages/Feedback/
   * @copyright (C) 2011 by iRail vzw/asbl 
   * @author: Werner Laurensse <el.lauwer aŧ gmail.com>
   * @license: AGPLv3
   */

include_once("rb.php");
include_once("resources/AResource.class.php");

/**
 * Class messages. Allows to push a feedback for a certain method.
 */
class Messages extends AResource {
    private $lang;
    private $system;
    private $time;
    private $direction;

    public static function getDoc() {
        return "Get all feedback on a certain package/resource";
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
	$this->$name = $val;
    }

    public static function getAllowedPrintMethods() {
        return array("php","xml","json","jsonp");
    }

    public static function getRequiredParameters() {
        return array("package", "resource");
    }

    public static function getParameters() {
        return array("package" => "The package name of the resource"
			"resource" => "The specific resource name");
    }
}
