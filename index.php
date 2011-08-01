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

require_once('glue.php');
require_once('handlers/Exceptions.class.php');
require_once('handlers/ErrorHandler.class.php');
require_once('handlers/ModuleHandler.class.php');
require_once('TDT.class.php'); //general purpose static class
require_once('Config.class.php'); //Configfile
require_once('factories/AResourceFactory.class.php');
require_once('factories/GenericResourceFactory.class.php');
require_once('factories/InstalledResourceFactory.class.php');
require_once('factories/RemoteResourceFactory.class.php');
require_once('resources/AResource.class.php');

//Autoloader for pages:
function __autoload($name){
    require_once('pages/' . $name . '.class.php');
}


set_error_handler('wrapper_handler');
date_default_timezone_set('UTC');

//map urls to a classname
$urls = array(
    '/' => 'Index',
    '/resources/' => 'Resources',
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
	$message = Messages();
	$result = $message -> call();
	$rootname = 'feedback';
	$printer = PrinterFactory::getPrinter($rootname, $_GET['format'], $result);
	$printer -> printAll();
    }

    function POST($matches) {
	require_once ('handlers/PostMessage.class.php');
	$post = new PostMessage();
	$post -> post();
    }
}

?>
