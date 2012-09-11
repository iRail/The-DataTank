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
        //query
        $query = "";
        $format = $matches["format"];
        
        if(isset($_GET["query"])){
            $query = $_GET["query"];
        }else{
            throw new Exception("No query given");
        }
        
        // (!) Documentation about the parser => see controllers/SQL/REAMDE.md
        
        // string -> filter syntax tree
        $parser = new SQLParser($query);
        $universalquery = $parser->interpret();
        
        // executer filter (returns Table)
        $interpreter=new UniversalInterpreter(new UniversalFilterTableManager());
        $result = $interpreter->interpret($universalquery);
                
        //convert format (Table->PhpObject)
        $converter = new TableToPhpObjectConverter();
        $object = $converter->getPhpObjectForTable($result);
        
        
        //pack everything in a new object
        $RESTresource="sqlquery";
        $o = new stdClass();
        $o->$RESTresource = $object;
        $result = $o;
        
        $formatterfactory = FormatterFactory::getInstance($format);//start content negotiation if the formatter factory doesn't exist

        
        $printer = $formatterfactory->getPrinter(strtolower("sqlquery"), $result);
        $printer->printAll();
    }

    function HEAD($matches){
        $query = "/";
        if(isset($matches["query"])){
            $query = $matches["query"];
        }
        $parser = new SPECTQLParser($query);
        $context = array(); // array of context variables

        $result = $parser->interpret($context);
        $formatterfactory = FormatterFactory::getInstance("about");//start content negotiation if the formatter factory doesn't exist
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
}
