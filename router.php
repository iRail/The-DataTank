<?php
/**
 * This file is the router. It's where all calls come in. It will accept a request en refer it elsewhere using glue.
 *
 * @package The-Datatank
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Werner Laurensse
 * @author Pieter Colpaert
 * @author Jan Vansteenlandt
 */

include_once('includes/glue.php');
include_once('includes/rb.php');
include_once('aspects/caching/Cache.class.php');
include_once('aspects/errors/Exceptions.class.php');
include_once('aspects/logging/ErrorLogger.class.php');
include_once('Controller.class.php');
include_once('TDT.class.php'); //general purpose static class
include_once('Config.class.php'); //Configfile
include_once('model/AResourceFactory.class.php');
include_once('model/GenericResourceFactory.class.php');
include_once('model/InstalledResourceFactory.class.php');
include_once('model/RemoteResourceFactory.class.php');
include_once('model/ResourcesModel.class.php');
include_once('model/resources/AResource.class.php');

set_error_handler('wrapper_handler');
date_default_timezone_set('UTC');
R::setup(Config::$DB,Config::$DB_USER,Config::$DB_PASSWORD);

//map urls to a classname
$urls = array(
    '/(?P<package>[^/]*)/(?P<resource>[^/]*)?/?(?P<RESTparameters>[^?]*)/?.*' => 'Controller'
);

//This function will do the magic. See glue.php
try {
    glue::stick($urls);
} 
catch(Exception $e){
    ErrorHandler::logException($e);
}

//TODO Werner: needs to move.
class FeedbackHandler {    
    function POST($matches) {
        require_once ('PostMessage.class.php');
        $post = new PostMessage();
        $post->post();
    }
}

?>