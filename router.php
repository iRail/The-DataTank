<?php
/**
 * This file is the router. It's where all calls come in. It will accept a request en refer it to the right Controller
 *
 * @package The-Datatank
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert
 * @author Jan Vansteenlandt
 */

include_once('includes/glue.php');
include_once('includes/rb.php');
include_once('aspects/caching/Cache.class.php');
include_once('aspects/errors/usage/Exceptions.php');
include_once('aspects/errors/system/Exceptions.php');
include_once('aspects/logging/ErrorLogger.class.php');
include_once('controllers/AController.class.php');
include_once('controllers/RController.class.php');
include_once('controllers/SPECTQLController.class.php');
include_once('controllers/SPECTQLIndex.class.php');
include_once('controllers/CUDController.class.php');
include_once('TDT.class.php'); //general purpose static class
include_once('Config.class.php'); //Configfile
include_once('RequestURI.class.php');
include_once('model/ResourcesModel.class.php');

include_once('model/semantics/OntologyProcessor.class.php');
include_once('model/semantics/RDFOutput.class.php');

define("RDFAPI_INCLUDE_DIR", "model/semantics/rdfapi-php/api/"); 
include_once(RDFAPI_INCLUDE_DIR . "RdfAPI.php");
include_once(RDFAPI_INCLUDE_DIR . "util/RdfUtil.php");
include_once(RDFAPI_INCLUDE_DIR . "vocabulary/VocabularyRes.php");
include_once(RDFAPI_INCLUDE_DIR . "vocabulary/VocabularyClass.php");
include_once(RDFAPI_INCLUDE_DIR . "resModel/ResModelP.php");
include_once(RDFAPI_INCLUDE_DIR . "model/DBase.php");


// The code for the wrapper_handler is in aspects/logging/ErrorLogger.class.php
set_error_handler('wrapper_handler');
// Time is always in UTC
date_default_timezone_set('UTC');
// Initialize the ORM with the right credentials
R::setup(Config::$DB,Config::$DB_USER,Config::$DB_PASSWORD);

//map urls to a classname
$urls = array(
    '/spectql/?' => 'SPECTQLIndex',
    //When a call is done to the TDTQL end-point, forward it to the TDTQLController
    '/spectql(?P<query>/.*)' => 'SPECTQLController',


    // Calling the Read- controller
    // This is a request on the representation
    // explanation of the last part of regex:
    // continue the REST parameters as long as no . is encountered. Continue format as long as no ? or end of URI occurs
    //    /package/resource/rest/para/meters.json?para=meter&filt=er
    '/(?P<package>[^/.]*)/(?P<resource>[^/.]*)/?(?P<RESTparameters>([^.])*)\.(?P<format>[^?]+).*' => 'RController',
    // Calling the Create, Update, Delete- controller

    // This is a request on the real-world object
    // examples of matches:
    //  PUT /package/
    //  POST /package/resource/property/
    //  POST /package/resource
    //  DELETE /package/resource
    // But also:
    //  GET /package/ - should give all resources in package in an exception
    //  GET /package/resource - should give a HTTP/1.1 303 See Other to the .about representation
    '/(?P<package>[^/.]*)/?(?P<resource>[^/.]*)?/?(?P<RESTparameters>[^?.]*)[^.]*' => 'CUDController'
);

//This function will do the magic. See includes/glue.php
try {
    glue::stick($urls);
} 
catch(Exception $e){
    ErrorHandler::logException($e);
}

?>
