<?php
/**
 * This file is the router. It will accept a request en refer it elsewhere using glue
 *
 * @package The-Datatank
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Werner Laurensse
 * @author Pieter Colpaert
 * @author Jan Vansteenlandt
 */

require_once ('glue.php');
require_once ('printer/PrinterFactory.php');
require_once ('handlers/Exceptions.class.php');
require_once ('handlers/RequestLogger.class.php');
require_once ('handlers/ErrorHandler.class.php');
require_once ('modules/ProxyModules.php');
require_once ('TDT.class.php');
require_once ('Config.class.php');

set_error_handler('wrapper_handler');
date_default_timezone_set('UTC');

/*
 * This is the former url-rewrite: it will map all urls to a certain class which will get the request
 */


$urls = array(
     '/' => 'Index',
     '/resources/' => 'Resources',
     '/docs/' => 'Docs',
     '/docs/(?P<module>.*?)/(?P<method>.*?)/.*' => 'DocPage',
     '/stats/' => 'Stats',
     '/Feedback/Messages/(?P<module>.*?)/(?P<method>.*?)/.*' => 'FeedbackHandler',
     '/(?P<module>.*?)/(?P<method>.*?)/(?P<resources>.*)' => 'ModuleHandler'
     );

//This function will do the magic. See glue.php
try {
	glue::stick($urls);
} catch(Exception $e) {
	ErrorHandler::logException($e);
}

//TODO: make an abstract class Page.class.php with method GET() and POST()
class Index {
	function GET() {
		require_once ('contents.php');
		include_once ("templates/TheDataTank/header.php");
		echo $index_content;
		include_once ("templates/TheDataTank/footer.php");
	}

	//give error on POST?
}

class Resources {
	function GET(){
		require_once("handlers/Resources.php");
	}
	//create a method in a certain module
	function POST(){
	    try{
	        
	   
		$cwd = getcwd(); 
		$path = $cwd."/modules/";
		$path.=$_POST["module"];
		// create the directory
		if(!is_dir($path)){		    
		  mkdir($path,0777);    
        }
		//methodfile
		//TODO Check if file exists already !!
		$methodFileHandle = fopen($path."/".$_POST["method"].".class.php", 'w'); //returns handle or FALSE when error
		if($methodFileHandle===FALSE){
			throw new ResourceTDTException("Can't create method.");
		}
		$templateFile = "/var/www/modules/methodtemplates/template.php";//
		filesize($templateFile);
		//echo "fileurl: " . $templateFile." ; ";
		$templatehandle = fopen($templateFile,'r');		
		//$data = fgets($templatehandle);//,filesize($templateFile)
		if(file_exists($templateFile)){
			echo "file exists !";
            echo filesize($templateFile);
			$data = fread($templatehandle,filesize($templateFile));
            if($data === FALSE){
                echo "oops";
            }else{
                echo $data;
            }
        }
		fclose($methodFileHandle);
        $dir = "/var/www/modules/methodtemplates/";
        if (is_dir($dir)){
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    echo $file."; ";
                }
                closedir($dh);
            }
        }
		fclose($templatehandle);		
		}catch(Exception $ex){
		    echo "Something went wrong";
		}
	}
}

class Docs {

	//TODO: put all these things in PagePrinters
	function GET() {
		require_once ("handlers/DocPrinter.php");
	}

}

class DocPage {
	function GET($matches) {
		require_once ("handlers/DocPagePrinter.php");
	}

}

class Stats {
	function GET() {
		require_once ("handlers/stats.php");
	}

}

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

class ModuleHandler {
     function GET($matches) {
	  RequestLogger::logRequest();
	  $result = new stdClass();
	  $module = $matches['module'];
	  $methodname = $matches['method'];
	  // Make sure that format is set and that the first letter is uppercase.
	  $headerlines = getallheaders();
	  if(isset($headerlines["Content-type"])) {
	       if(preg_match('/.*\/(.*?);.*?/', $headerlines["Content-type"], $matches)) {
		    $match = $matches[1];
		    //See php doc for this [0] contains the full match, 1 contains the first group
		    $_GET['format'] = ucfirst(strtolower($match));
	       }
	       
	  } elseif(!isset($_GET['format'])) {
	       $_GET['format'] = 'Xml';
	  } else {
	       $_GET['format'] = ucfirst(strtolower($_GET['format']));
	  }

	  $resources=array();
	  if(isset($matches['resources'])){
	       $resources = explode("/",$matches['resources']);
	       array_pop($resources); // remove the last elemenet because that just contains the GET parameters
	  }
	  

	  if(file_exists("modules/$module/$methodname.class.php")) {
	       //get the new method
	       include_once ("modules/$module/$methodname.class.php");
	       $method = new $methodname();

	       //Now let's separate the required parameters from the filters
	       

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
	       throw new MethodOrModuleNotFoundTDTException($module . "/" .$methodname);
	  }

	  //Support REST actions:
	  //allow to specify deeper requests
	  if(sizeof($resources) > 0){
	       foreach($resources as $resource){
		    if(is_object($result) && isset($result->$resource)){
			 $result = $result->$resource;
		    }elseif(is_array($result) && isset($result[$resource])){
			 $result = $result[$resource];
		    }else{
			 break;//on error, just return what we have so far
		    }
	       }
	       if(!is_object($result)){
		    $o = new stdClass();
		    $resource = $resources[sizeof($resources)-1];
		    $o->$resource = $result;
		    $result = $o;
	       }
	  }

	  $rootname = $methodname;
	  $rootname = strtolower($rootname);
 
	  $printer = PrinterFactory::getPrinter($rootname, $_GET['format'], $result);
	  $printer->printAll();
     }
}

?>
