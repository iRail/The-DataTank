<?php

include_once ("UsageTDTException.class.php");

/**
 * This file contains all the Exceptions specifically made for the DataTank.
 * @package The-Datatank/error
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <Jan@iRail.be>
 * @author Pieter Colpaert   <pieter@iRail.be>
 */

/**
 * These are HTTP 400 errors: Parameter or Resources not found
 */

/**
 * This class reprents an exception which is thrown when a given resource or package is not valid.
 */
class ResourceOrPackageNotFoundTDTException extends UsageTDTException {
    public static function getDoc() {
        return "When a resource or package is not found this Exception is thrown. The constructor expects the name of the package or the name of the resource. This is a 451 error: not found";
    }
    
    public function __construct($m) {
        parent::__construct("Resource or package not found: " . $m);
    }
    
    public static $error = 451;
}

/**
 * This class reprents an exception which is thrown when the resource given is not a valid resource.
 */
class NotAResourceTDTException extends UsageTDTException {
    public static function getDoc() {
        return "This function is not a resource";
    }
    
    public function __construct() {
        parent::__construct("Not a resource");
    }
    
    public static $error = 452;
}

/**
 * This class reprents an exception which is thrown when a given format is not a valid one.
 */
class FormatNotAllowedTDTException extends UsageTDTException {
    
    public static function getDoc() {
        return "When a certain format is given with the request and it is not allowed by the resource. This exception is thrown, and the allowed formats are show to the user.";
    }
    
    public function __construct($m, $format) { // format = array of allowed formats
        $message = "Format not allowed: " . $m . ". Allowed formats are : <br> ";
        foreach ($format as $format) {
            $message = $message . " $format <br>";
        }
        
        parent::__construct($message);
    }
    
    public static $error = 453;
}

class FormatNotFoundTDTException extends UsageTDTException {
    
    public static function getDoc() {
        return "Format does not exist";
    }
    
    public function __construct($m) {
        $message = "Formatter not found: " . $m;
        parent::__construct($message);
    }
    
    public static $error = 460;
}

/**
 * This class reprents an exception which is thrown when a given parameter is not found or incorrect.
 */
class ParameterTDTException extends UsageTDTException {
    public static function getDoc() {
        return "This exception is thrown when a parameter is incorrect.";
    }
    
    public static $error = 454;
    
    public function __construct($parameter) {
        parent::__construct("Parameter not found or incorrect: " . $parameter . ". Try adding /". $parameter . " it in front of the format in your URL.");
    }
}

/**
 * This class reprents an exception which is thrown when a given resource or package is not valid.
 */
class ParameterDoesntExistTDTException extends UsageTDTException {
    public static function getDoc() {
        return "This exception is thrown when a parameter does not exist. The constructor needs a parameter";
    }
    
    public static $error = 455;
    
    public function __construct($parameter) {
        parent::__construct("Parameter does not exist: " . $parameter);
    }
}


/**
 * This class reprents an exception which is thrown when a given resource or package is not valid.
 */
class FilterTDTException extends UsageTDTException {
    public static function getDoc() {
        return "This exception is thrown when an error occured while applying a filter to our result.";
    }
    
    public static $error = 456;
    
    public function __construct($message) {
        parent::__construct("Something went wrong while applying the filter on the result: " . $message);
    }
}

/**
 * This class reprents an exception which is thrown when a given resource or package is not valid.
 */
class RESTTDTException extends UsageTDTException {
    public static function getDoc() {
        return "This exception is thrown when an error occured while applying a filter to our result.";
    }
    
    public static $error = 457;
    
    public function __construct($message) {
        parent::__construct("The REST-ful path given was incorrect: " . $message);
    }
}

/**
 * This class reprents an exception which is thrown when a given resource or package is not valid.
 */
class ResourceAdditionTDTException extends UsageTDTException {
    public static function getDoc() {
        return "This exception is thrown when an error while trying to add a resource.";
    }
    
    public static $error = 458;
    
    public function __construct($message) {
        parent::__construct("An error occured while trying to add a resource: " . $message);
    }
}

/**
 * This class reprents an exception which is thrown when a user isn't allowed to make a certain action
 */
class AuthenticationTDTException extends UsageTDTException {
    public static function getDoc() {
        return "This exception is thrown when a user performs an non-allowed action.";
    }
    
    public static $error = 459;
    
    public function __construct($message) {
        parent::__construct("User authentication failed: " . $message);
    }
}

/**
 * This class represents an exception which is thrown when an update on a resource isn't valid.
 */
class ResourceUpdateTDTException extends UsageTDTException {
    public static function getDoc() {
        return "This exception is thrown when an update on a resource isn't valid.";
    }
    
    public static $error = 460;
    
    public function __construct($message) {
        parent::__construct("An error occured while trying to update a resource: " . $message);
    }
}

class RdfTDTException extends UsageTDTException {
    public static function getDoc() {
        return "This exception is thrown when an update on a RDF mapping isn't valid.";
    }
    
    public static $error = 461;
    
    public function __construct($message) {
        parent::__construct("An error occured while trying to update a RDF mapping: " . $message);
    }
}

class DeleterTDTException extends UsageTDTException {
    public static function getDoc() {
        return "Cannot delete this resource";
    }
    
    public static $error = 462;
    
    public function __construct($message) {
        parent::__construct("An error occured while trying to delete a resource: " . $message);
    }
}

/**
 * This class reprents an exception which is thrown when a given resource or package is not valid.
 */
class OntologyAdditionTDTException extends UsageTDTException {
    public static function getDoc() {
        return "This exception is thrown when an error while trying to add an ontology or ontology information.";
    }
    
    public static $error = 463;
    
    public function __construct($message) {
        parent::__construct("An error occured while trying to add to an ontology: " . $message);
    }
}


class OntologyUpdateTDTException extends UsageTDTException {
    public static function getDoc() {
        return "This exception is thrown when an update on an ontology isn't valid.";
    }
    
    public static $error = 464;
    
    public function __construct($message) {
        parent::__construct("An error occured while trying to update an ontology: " . $message);
    }
}

class OntologyDeleterTDTException extends UsageTDTException {
    public static function getDoc() {
        return "Cannot delete this ontology or ontology entry";
    }
    
    public static $error = 465;
    
    public function __construct($message) {
        parent::__construct("An error occured while trying to delete an ontology: " . $message);
    }
}

/**
 * This class reprents an exception which is thrown when a given path is not valid.
 */
class OntologyPathDoesntExistTDTException extends UsageTDTException {
    public static function getDoc() {
        return "This exception is thrown when a path in an ontology does not exist. The path needs to be created before a mapping can occur.";
    }
    
    public static $error = 466;
    
    public function __construct($parameter) {
        parent::__construct("Path in ontology does not exist: " . $parameter);
    }
}
?>