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
include_once("controllers/sql/SQLParser.class.php");
include_once("model/ResourcesModel.class.php");
include_once("model/DBQueries.class.php");


//debug
include_once("universalfilter/interpreter/UniversalInterpreter.php");
include_once("universalfilter/tablemanager/UniversalFilterTableManager.class.php");

include_once("universalfilter/tablemanager/tools/TableToPhpObjectConverter.class.php");

class SQLController extends AController {

    /**
     * This implements the GET
     * 
     */
    function GET($matches) {
        //query
        $query = "/";
        $format = $matches["format"];
        if(isset($matches["query"])){
            $query = $matches["query"];
        }else{
            throw new Exception("No query given");
        }
        $parser = new SQLParser($query);
        
        $result = $parser->interpret();
        
//        var_dump($result);
        
//        echo "<br/>";
//        echo "-end parser- -start interpreter- ";
//        echo "<br/>";
        
        
        $interpreter=new UniversalInterpreter();
        $executer = $interpreter->findExecuterFor($result);
        $env = $executer->execute($result, $interpreter);
        
//        echo "<br/>";
//        echo "-end interpreter- -start result- ";
//        echo "<br/>";
//        
//        var_dump($env->getLastTable());
//        
//        echo "<br/>";
//        echo "-end result- ";
//        echo "<br/>";
//        exit();
        
        //convert format
        $converter = new TableToPhpObjectConverter();
        
        $object = $converter->getPhpObjectForTable($env->getLastTable());
        
        
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

?>
