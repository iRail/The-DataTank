<?php

include_once ("SystemTDTException.class.php");

/**
 * This file contains all the Exceptions specifically made for the DataTank.
 * @package The-Datatank/aspects/errors/system
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <Jan@iRail.be>
 * @author Pieter Colpaert   <pieter@iRail.be>
 */

/**
 * These are HTTP 500 errors: internal server errors
 */

/**
 * This class reprents an exception which is thrown when a given resource or package is not valid.
 */
class CouldNotGetDataTDTException extends SystemTDTException {
    public static function getDoc() {
        return "This exception is thrown when the data could not be resolved.";
    }
    
    public static $error = 551;
    
    public function __construct($datasourcename) {
        parent::__construct("This could not be resolved: " . $datasourcename);
    }
}

/**
 * This class reprents an exception which is thrown when a given resource or package is not valid.
 */
class InternalServerTDTException extends SystemTDTException {
    public static function getDoc() {
        return "This exception is thrown when a fatal error occurs. This due unexpected errors i.e. a file that couldn't be opened." . "For further information check /var/log/apache2/error.log";
    }
    
    public static $error = 552;
    
    public function __construct($message) {
        parent::__construct($message);
    }
}

/**
 * This class reprents an exception which is thrown when a given resource or package is not valid.
 */
class RemoteServerTDTException extends SystemTDTException {
    public static function getDoc() {
        return "This error is thrown because a proxy call has gone wrong." . "This probably due to remoteserver problem.";
    }
    
    public static $error = 553;
    
    public function __construct($message) {
        parent::__construct($message);
    }
}

/**
 * This class reprents an exception which is thrown when a given resource or package is not valid.
 */
class NoFormatterTDTException extends SystemTDTException {
    public static function getDoc() {
        return "No formatter is available or something went wrong in the Formatter class";
    }
    
    public static $error = 554;
    
    public function __construct() {
        parent::__construct("Formatter error. Check the value of your format parameter");
    }
}

/**
 * This class reprents an exception which is thrown when a given resource or package is not valid.
 */
class BadResourceCallTDTException extends SystemTDTException {
    public static function getDoc() {
        return "Bad resource call";
    }
    
    public static $error = 555;
    
    public function __construct($message) {
        parent::__construct($message);
    }
}

/**
 * This class reprents an exception which is thrown when a given resource or package is not valid.
 */
class NotFoundTDTException extends SystemTDTException {
    public static function getDoc() {
        return "Class not found!";
    }
    
    public static $error = 556;
    
    public function __construct($message) {
        parent::__construct($message);
    }
}
/**
 * This class reprents an exception which is thrown when a given resource or package is not valid.
 */
class CouldNotParseUrlTDTException extends SystemTDTException {
    public static function getDoc() {
        return "When a wrong url is given or when the server cannot handle or url";
    }
    
    public static $error = 557;
    
    public function __construct($url) {
        parent::__construct("Could not parse url: " . $url);
    }
}

/**
 * This class reprents an exception which is thrown when a given resource or package is not valid.
 */
class HttpOutTDTException extends SystemTDTException {
    public static function getDoc() {
        return "We failed contacting an external server";
    }
    
    public static $error = 558;
    
    public function __construct($url) {
        parent::__construct("Could not connect to " . $url);
    }
}

/**
 * This class reprents an exception which is thrown when a given resource or package is not valid.
 */
class InternalFormatterTDTException extends SystemTDTException {
    public static function getDoc() {
        return "Something is wrong in the object - Could not format";
    }
    
    public static $error = 559;
    
    public function __construct($msg) {
        parent::__construct("Object gives weird formatteroutput - fix your package: " . $msg);
    }
}

/**
 * This class represents an exception which is trhown when a database related error occurs.
 */
class DatabaseTDTException extends SystemTDTException {
    public static function getDoc() {
        return "Something went wrong whilst contacting the database.";
    }
    
    public static $error = 560;
    
    public function __construct($msg) {
        parent::__construct("Something went wrong whilst contact the database: " . $msg);
    }
}

/**
 * This class represents an exception which thrown when the creation of a resource fails.
 */
class ResourceTDTException extends SystemTDTException {
    public static function getDoc() {
        return "When a creation of a resource fails";
    }
    
    public static $error = 561;
    
    public function __construct($msg) {
        parent::__construct("Something went wrong: " . $msg);
    }
}

/**
 * This class represents an exception which thrown when the creation of a resource fails.
 */
class CacheTDTException extends SystemTDTException {
    public static function getDoc() {
        return "There was an error with the cache";
    }
    
    public static $error = 562;
    
    public function __construct($msg) {
        parent::__construct("Cache error: " . $msg);
    }
}

class RepresentationCUDCallTDTException extends SystemTDTException {
    public static function getDoc() {
        return "Happens when you POST, PUT or DELETE on a representation";
    }
    
    public static $error = 571;
    
    public function __construct() {
        parent::__construct("You cannot write to a representation. Use TDTInfo/Resources for CUD operations.");
    }
}

class NoResourceGivenTDTException extends SystemTDTException {
    public static function getDoc() {
        return "No resource given.";
    }
    
    public static $error = 572;
    
    public function __construct($resources) {
        $message = "";
        
        foreach ($resources as $resource => $v) {
            $message .= $resource . ",";
        }
        $message = rtrim($message, ",");
        parent::__construct("You didn't specify a resource. The resources in this package are: " . $message);
    }
}

?>