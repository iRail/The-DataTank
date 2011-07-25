<?php
  /* Copyright (C) 2011 by iRail vzw/asbl
   * Author: Werner Laurensse
   * License: AGPLv3
   */

  /**
   * This file contains the first frontier that dispatches requests to different method calls. This file will receive the call
   * and return the result of that call.
   * @package The-Datatank
   * @copyright (C) 2011 by iRail vzw/asbl
   * @license AGPLv3
   * @author Werner Laurensse
   */


require_once('glue.php');
require_once("printer/PrinterFactory.php");
require_once("error/Exceptions.class.php");
require_once("requests/RequestLogger.class.php");
require_once("error/ErrorHandler.class.php");
require_once("modules/ProxyModules.php");
require_once("TDT.class.php");
require_once('Config.class.php');

set_error_handler("wrapper_handler");
date_default_timezone_set("UTC");


$urls = array(
     '/' => 'Index',
     '/docs/' => 'Docs',
     '/stats/' => 'Stats',
     '/Feedback/Messages/(.*)/(.*)/' => 'FeedbackHandler',
     '/(?P<module>.*?)/(?P<method>.*?)/.*' => 'ModuleHandler'
     );

class Index {
     function GET() {
	  require_once('contents.php');
	  include_once("templates/TheDataTank/header.php");
	  echo $index_content;
	  include_once("templates/TheDataTank/footer.php");
     }
}

class Docs {
     function GET() {
	  require_once("docs/DocPrinter.php");
     }
}

class Stats {
     function GET() {
	  require_once("stats/index.php");
     }
}

class FeedbackHandler {
     function GET() {

     }
}

class ModuleHandler {
     function GET($matches) {
	  //TODO add try and catch, throw error, logging.
	  try {
	       $result;
	       $module = $matches['module'];
	       $methodname = $matches['method'];
	       // Make sure that format is set and that the first letter is uppercase.
	       if (!isset($_GET['format'])) {
		    $_GET['format'] = 'Xml';
	       } else {
		    $_GET['format'] = ucfirst(strtolower($_GET['format']));
	       }

	       if(file_exists("modules/$module/$methodname.class.php")) {
		    //get the new method
		    include_once ("modules/$module/$methodname.class.php");
		    $method = new $methodname();

		    // check if the given format is allowed by the method
		    // if not, throw an exception and return the allowed formats
		    // to the user.
		    if((!in_array(strtolower($_GET['format']),$method->getAllowedPrintMethods()))){
			 throw new FormatNotAllowedTDTException($_GET['format'],$method::getAllowedPrintMethods());
		    }

		    //execute the method when no error occured
		    $result = $method->call();
	       } else if (array_key_exists($module,ProxyModules::$modules)) {
		    //If we cannot find the modulename locally, we're going to search for it through proxy
		    $result = ProxyModules::call($module, $methodname, $_GET);		
	       } else {
		    echo 'test: ' . $module . $methodname;
		    throw new MethodOrModuleNotFoundTDTException($module . "/" .$methodname);
	       }

	       $rootname = $methodname;
	       $rootname = strtolower($rootname);
	       $printer = PrinterFactory::getPrinter($rootname, $_GET['format'], $rootname, $result);
	       $printer->printAll();
	  } catch(Exception $e) {
	       ErrorHandler::logException($e);
	  }

	  RequestLogger::logRequest();
     }
}

glue::stick($urls);
?>
