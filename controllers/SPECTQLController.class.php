<?php
/**
 * The controller will handle all SPECTQL requests
 *
 * If it checked all required parameters, checked the format, it will perform the call and get a result. This result is a formatter returned from the FormatterFactory
 *
 * @package The-Datatank/controllers
 * @copyright (C) 2011 by OKFN chapter Belgium vzw/asbl
 * @license LGPL
 * @author Pieter Colpaert
 * @organisation Hogent
 */
include_once("custom/formatters/FormatterFactory.class.php");
include_once("controllers/spectql/SPECTQLParser.class.php");
include_once("model/ResourcesModel.class.php");
include_once("model/DBQueries.class.php");


include_once("universalfilter/interpreter/UniversalInterpreter.php");
include_once("universalfilter/tablemanager/implementation/UniversalFilterTableManager.class.php");
include_once("universalfilter/tablemanager/implementation/tools/TableToPhpObjectConverter.class.php");

class SPECTQLController extends AController {

    /**
     * This implements the GET
     * 
     */
    function GET($matches) {
        $query = "/";
        if(isset($matches["query"])){
            $query = $matches["query"];
        }

        // split off the format of the query, if passed
        $matches = array();
        $format = "about";
        if(preg_match("/:[a-zA-Z]+/",$query,$matches)){
            $format = ltrim($matches[0],":");
        }
        
        $parser = new SPECTQLParser($query);
        $context = array(); // array of context variables

        $universalquery = $parser->interpret($context);

        $interpreter=new UniversalInterpreter(new UniversalFilterTableManager());
        $result = $interpreter->interpret($universalquery);
        $converter = new TableToPhpObjectConverter();
        
        $object = $converter->getPhpObjectForTable($result);
        
        
        //pack everything in a new object
        $RESTresource="spectqlquery";
        $o = new stdClass();
        $o->$RESTresource = $object;
        $result = $o;


        $formatterfactory = FormatterFactory::getInstance($format);//start content negotiation if the formatter factory doesn't exist
        $rootname = "spectql";

        
        $printer = $formatterfactory->getPrinter(strtolower($rootname), $result);
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
