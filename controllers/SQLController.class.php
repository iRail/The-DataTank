<?php

/**
 * The controller will handle all SQL requests
 *
 * @package The-Datatank/controllers
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jeroen Penninck
 */
include_once("custom/formatters/FormatterFactory.class.php");
include_once("controllers/SQL/SQLParser.class.php");
include_once("model/ResourcesModel.class.php");
include_once("model/DBQueries.class.php");


//imports for the evaluation of the universalfilter
include_once("universalfilter/interpreter/UniversalInterpreter.php");

include_once("universalfilter/tablemanager/implementation/UniversalFilterTableManager.class.php");
include_once("universalfilter/tablemanager/implementation/tools/TableToPhpObjectConverter.class.php");

class SQLController extends AController {

    /**
     * This implements the GET
     * 
     */
    function GET($matches) {
        //setting the default timezone
        if (isset(Config::$TIMEZONE)) {
            date_default_timezone_set(Config::$TIMEZONE);
        }

        //query
        $query = "";
        $format = $matches["format"];

        if (isset($_GET["query"])) {
            $query = $_GET["query"];
        } else {
            throw new Exception("No query given");
        }

        /*
         * We have to make sure the TDTAdmin resources
         * are still hidden from normal users. Using a regex, 
         * we're going to find out if the TDTAdmin has been adressed.
         */

        if (preg_match("/.*from TDTAdmin.*/i", $_GET["query"]) == 1) {
            if(!$this->isAuthenticated()){
                //we need to be authenticated
                header('WWW-Authenticate: Basic realm="' . Config::$HOSTNAME . Config::$SUBDIR . '"');
                header('HTTP/1.0 401 Unauthorized');
                exit();
            }
        }

        // (!) Documentation about the parser => see controllers/SQL/REAMDE.md
        // string -> filter syntax tree
        $parser = new SQLParser($query);
        $universalquery = $parser->interpret();

        if (isset($_GET["printdebug"]) && $_GET["printdebug"] == "true") {
            $printer = new TreePrinter();
            $printer->printString($universalquery);
        }

        // executer filter (returns Table)
        $interpreter = new UniversalInterpreter(new UniversalFilterTableManager());
        $result = $interpreter->interpret($universalquery);

        //convert format (Table->PhpObject)
        $converter = new TableToPhpObjectConverter();
        $object = $converter->getPhpObjectForTable($result);


        //pack everything in a new object
        $RESTresource = "sqlquery";
        $o = new stdClass();
        $o->$RESTresource = $object;
        $result = $o;

        $formatterfactory = FormatterFactory::getInstance($format); //start content negotiation if the formatter factory doesn't exist
        $formatterfactory->setFormat($format);

        $printer = $formatterfactory->getPrinter(strtolower("sqlquery"), $result);
        $printer->printAll();
    }

    function HEAD($matches) {
        $query = "/";
        if (isset($matches["query"])) {
            $query = $matches["query"];
        }
        $parser = new SPECTQLParser($query);
        $context = array(); // array of context variables

        $result = $parser->interpret($context);
        $formatterfactory = FormatterFactory::getInstance("about"); //start content negotiation if the formatter factory doesn't exist
        $formatterfactory->setFormat($format);
        $rootname = "spectql";


        $printer = $formatterfactory->getPrinter(strtolower($rootname), $result);
        $printer->printHeader();
    }

    /**
     * You cannot PUT on a representation
     */
    function PUT($matches) {
        throw new RepresentationCUDCallTDTException();
    }

    /**
     * You cannot delete a representation
     */
    public function DELETE($matches) {
        throw new RepresentationCUDCallTDTException();
    }

    /**
     * You cannot use post on a representation
     */
    public function POST($matches) {
        throw new RepresentationCUDCallTDTException();
    }

    /**
     * You cannot use patch a representation
     */
    public function PATCH($matches) {
        throw new RepresentationCUDCallTDTException();
    }
    
    private function isAuthenticated() {
        return isset($_SERVER['PHP_AUTH_USER']) && $_SERVER['PHP_AUTH_USER'] == Config::$API_USER && $_SERVER['PHP_AUTH_PW'] == Config::$API_PASSWD;
    }

}
