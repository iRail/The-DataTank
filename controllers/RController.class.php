<?php
/**
 * This controller will handle the GET request (RController = ReadController)
 * Returning objects of resources, or throwing an exception if something went wrong
 *
 * @package The-Datatank/controllers
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert
 * @author Jan Vansteenlandt
 */
include_once('custom/formatters/FormatterFactory.class.php');
include_once('aspects/logging/RequestLogger.class.php');
include_once('model/filters/FilterFactory.class.php');
include_once('model/ResourcesModel.class.php');
include_once("model/DBQueries.class.php");

class RController extends AController {

    private $formatterfactory;

    function GET($matches) {
        //always required: a package and a resource. 
        $package = trim($matches['package']);
        $resourcename = trim($matches['resource']);
        //This will create an instance of a factory depending on which format is set
        $this->formatterfactory = FormatterFactory::getInstance($matches["format"]);

        //Get an instance of our resourcesmodel
        $model = ResourcesModel::getInstance();
        //ask the model for our documentation: access to all packages and resources!

        $doc = $model->getAllDoc();

        
        if(!isset($doc->$package) || !isset($doc->$package->$resourcename)){
            throw new ResourceOrPackageNotFoundTDTException("please check if $package and $resourcename are a correct package-resource pair");
        }        

        // get the RESTful parameters from the request
        $RESTparameters = array();
        if (isset($matches['RESTparameters']) && $matches['RESTparameters'] != "") {
            $RESTparameters = explode("/", rtrim($matches['RESTparameters'], "/"));
        }

        $parameters = $_GET;
        
        
            
        foreach ($doc->$package->$resourcename->requiredparameters as $parameter) {
            //set the parameter of the method
                
            if (!isset($RESTparameters[0])) {
                throw new ParameterTDTException($parameter);
            }
            $parameters[$parameter] = $RESTparameters[0];
            //removes the first element and reindex the array - this way we'll only keep the object specifiers (RESTful filtering) in this array
            array_shift($RESTparameters);
        }
       
        
        $result = $model->readResource($package, $resourcename, $parameters, $RESTparameters);
        
        //maybe the resource reinitialised the database, so let's set it up again with our config, just to be sure.
        R::setup(Config::$DB, Config::$DB_USER, Config::$DB_PASSWORD);

        // apply RESTFilter
        $subresources = array();
        $filterfactory = FilterFactory::getInstance();

        if (sizeof($RESTparameters) > 0) {
            if (!(is_subclass_of($result, 'Model') || is_a($result, 'Model'))) {
                $RESTFilter = $filterfactory->getFilter("RESTFilter", $RESTparameters);
                $resultset = $RESTFilter->filter($result);
                $subresources = $resultset->subresources;
                $result = $resultset->result;
            }
        }
        // Apply Lookup filter if asked, this has been implemented according to the 
        // Open Search Specifications

        if (isset($_GET["filterBy"]) && isset($_GET["filterValue"])) {
            if (is_array($result)) {
                $filterparameters = array();
                $filterparameters["filterBy"] = $_GET["filterBy"];
                $filterparameters["filterValue"] = $_GET["filterValue"];
                if (isset($_GET["filterOp"])) {
                    $filterparameters["filterOp"] = $_GET["filterOp"];
                }

                $searchFilter = $filterfactory->getFilter("SearchFilter", $filterparameters);
                $result = $searchFilter->filter($result);
            }
        }

        //pack everything in a new object
        $o = new stdClass();
        $RESTresource = "";
        if (sizeof($RESTparameters) > 0) {
            $RESTresource = $RESTparameters[sizeof($RESTparameters) - 1];
        } else {
            $RESTresource = $resourcename;
        }
        $o->$RESTresource = $result;
        $result = $o;
        
        // get the according formatter from the factory
        $printer = $this->formatterfactory->getPrinter(strtolower($resourcename), $result);
        $printer->printAll();
        RequestLogger::logRequest();
    }

    public function HEAD($matches){
                //always required: a package and a resource. 
        $package = trim($matches['package']);
        $resourcename = trim($matches['resource']);
        //This will create an instance of a factory depending on which format is set
        $this->formatterfactory = FormatterFactory::getInstance($matches["format"]);

        //Get an instance of our resourcesmodel
        $model = ResourcesModel::getInstance();
        //ask the model for our documentation: access to all packages and resources!

        $doc = $model->getAllDoc();

        
        if(!isset($doc->$package) || !isset($doc->$package->$resourcename)){
            throw new ResourceOrPackageNotFoundTDTException("please check if $package and $resourcename are a correct package-resource pair");
        }        

        // get the RESTful parameters from the request
        $RESTparameters = array();
        if (isset($matches['RESTparameters']) && $matches['RESTparameters'] != "") {
            $RESTparameters = explode("/", rtrim($matches['RESTparameters'], "/"));
        }

        $parameters = $_GET;
        
        
            
        foreach ($doc->$package->$resourcename->requiredparameters as $parameter) {
            //set the parameter of the method
                
            if (!isset($RESTparameters[0])) {
                throw new ParameterTDTException($parameter);
            }
            $parameters[$parameter] = $RESTparameters[0];
            //removes the first element and reindex the array - this way we'll only keep the object specifiers (RESTful filtering) in this array
            array_shift($RESTparameters);
        }
       
        
        $result = $model->readResource($package, $resourcename, $parameters, $RESTparameters);
        
        //maybe the resource reinitialised the database, so let's set it up again with our config, just to be sure.
        R::setup(Config::$DB, Config::$DB_USER, Config::$DB_PASSWORD);

        // apply RESTFilter
        $subresources = array();
        $filterfactory = FilterFactory::getInstance();

        if (sizeof($RESTparameters) > 0) {
            if (!(is_subclass_of($result, 'Model') || is_a($result, 'Model'))) {
                $RESTFilter = $filterfactory->getFilter("RESTFilter", $RESTparameters);
                $resultset = $RESTFilter->filter($result);
                $subresources = $resultset->subresources;
                $result = $resultset->result;
            }
        }
        // Apply Lookup filter if asked, this has been implemented according to the 
        // Open Search Specifications

        if (isset($_GET["filterBy"]) && isset($_GET["filterValue"])) {
            if (is_array($result)) {
                $filterparameters = array();
                $filterparameters["filterBy"] = $_GET["filterBy"];
                $filterparameters["filterValue"] = $_GET["filterValue"];
                if (isset($_GET["filterOp"])) {
                    $filterparameters["filterOp"] = $_GET["filterOp"];
                }

                $searchFilter = $filterfactory->getFilter("SearchFilter", $filterparameters);
                $result = $searchFilter->filter($result);
            }
        }

        //pack everything in a new object
        $o = new stdClass();
        $RESTresource = "";
        if (sizeof($RESTparameters) > 0) {
            $RESTresource = $RESTparameters[sizeof($RESTparameters) - 1];
        } else {
            $RESTresource = $resourcename;
        }
        $o->$RESTresource = $result;
        $result = $o;
        
        // get the according formatter from the factory
        $printer = $this->formatterfactory->getPrinter(strtolower($resourcename), $result);
        $printer->printHeader();
        RequestLogger::logRequest();
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
}
?>
