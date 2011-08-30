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

include_once('glue.php');
include_once('rb.php');
include_once('caching/Cache.class.php');
include_once('handlers/Exceptions.class.php');
include_once('handlers/ErrorHandler.class.php');
include_once('handlers/ModuleHandler.class.php');
include_once('TDT.class.php'); //general purpose static class
include_once('Config.class.php'); //Configfile
include_once('factories/AResourceFactory.class.php');
include_once('factories/GenericResourceFactory.class.php');
include_once('factories/InstalledResourceFactory.class.php');
include_once('factories/RemoteResourceFactory.class.php');
include_once('factories/AllResourceFactory.class.php');
include_once('resources/AResource.class.php');

//Autoloader for pages:
function __autoload($name){
    if(file_exists('pages/' . $name . '.class.php')) {
        include_once('pages/' . $name . '.class.php');
    }
}

set_error_handler('wrapper_handler');
date_default_timezone_set('UTC');

//map urls to a classname
$urls = array(
    '/' => 'Index',
    '/docs/' => 'Docs',
    '/docs/(?P<module>.*?)/(?P<resource>.*?)/.*' => 'DocPage',
    '/stats/' => 'Stats',
    '/Feedback/Messages/(?P<module>.*?)/(?P<method>.*?)/.*' => 'FeedbackHandler',
    '/(?P<module>.*?)/(?P<resource>.*?)/(?P<RESTparameters>.*)' => 'ModuleHandler'
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
    function GET($matches) {
        require_once ('modules/Feedback/Messages.class.php');
        $message = new Messages();
        $result = $message->call();
        $rootname = 'feedback';
        $printer = PrinterFactory::getPrinter($rootname, $_GET['format'], $result);
        $printer->printAll();
    }
    
    function POST($matches) {
        require_once ('handlers/PostMessage.class.php');
        $post = new PostMessage();
        $post->post();
    }
}

?>