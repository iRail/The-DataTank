<?php
require_once('glue.php');

$urls = array(
    '/' => 'Index',
    '/docs/' => 'Docs',
    '/stats/' => 'Stats',
    '/Feedback/Messages/(.*)/(.*)/' => 'FeedbackHandler',
    '/(?P<number>.*?)/(?P<number>.*?)/' => 'ModuleHandler'
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
    function GET() {
        // Make sure that format is set and that the first letter is uppercase.
        if (!isset($_GET['format'])) {
            $_POST['format'] = Xml;
        } else {
            $_POST['format'] = ucfirst(strtolower($_POST['format']));
        }

        if(file_exists("modules/$module/$methodname.class.php")) {
		    //get the new method
		    include_once ("modules/$module/$methodname.class.php");
		    $method = new $methodname();

		    // check if the given format is allowed by the method
		    // if not, throw an exception and return the allowed formats
		    // to the user.
		    if((!in_array(strtolower($format),$method->getAllowedPrintMethods()))){
			    throw new FormatNotAllowedTDTException($format,$method::getAllowedPrintMethods());
            }

		    //execute the method when no error occured
		    $result = $method->call();
        } else if (array_key_exists($module,ProxyModules::$modules)) {
		    //If we cannot find the modulename locally, we're going to search for it through proxy
		    unset($_GET["method"]);
		    unset($_GET["module"]);
		    $result = ProxyModules::call($module, $methodname, $_GET);		
        } else {
            echo 'test: ' . $module . $methodname;
		    throw new MethodOrModuleNotFoundTDTException($module . "/" .$methodname);
        }
    }
}

glue::stick($urls);
?>
