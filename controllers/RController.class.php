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
        
        /**
         * Even GET operations on TDTAdmin need to be authenticated!
         */

        if($package == "TDTAdmin"){
            //we need to be authenticated
            if (!$this->isAuthenticated()) {
                header('WWW-Authenticate: Basic realm="' . Config::$HOSTNAME . Config::$SUBDIR . '"');
                header('HTTP/1.0 401 Unauthorized');
                exit();
            }
        }

        /**
         * If there is only a package passed, pass along a list of its resources
         */

        //Get an instance of our resourcesmodel
        $model = ResourcesModel::getInstance();
        $doc = $model->getAllDoc();
        if ($resourcename == "") {
            if (isset($doc->$package)) {
                $resourcenames = get_object_vars($doc->$package);
                $linkObject = new StdClass();
                $links = array();
                foreach($resourcenames as $resourcename => $value){
                    
                    $link = Config::$HOSTNAME . Config::$SUBDIR . $package . "/".  $resourcename;
                    $links[] = $link;
                    $linkObject->$package = $links;
                }
                //This will create an instance of a factory depending on which format is set
                $this->formatterfactory = FormatterFactory::getInstance($matches["format"]);

                $printer = $this->formatterfactory->getPrinter(strtolower($package), $linkObject);
                $printer->printAll();
                RequestLogger::logRequest();
            }else if($model->hasPackage($package)){
                echo "No resources are listed for this package <br>";
            } else {
                echo "This package name ( $package ) has not been created yet.";
            }
            exit();
        }
        
        /**
         * At this stage a package and a resource have been passed, lets check if they exists, and if so lets call the read()
         * action and return the result.
         */
        
        //This will create an instance of a factory depending on which format is set
        $this->formatterfactory = FormatterFactory::getInstance($matches["format"]);
        
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
        // dont log requests to visualizations, these visualizations will trigger another request to (mostly) the json 
        // representation of the resource
        if(!$this->isVisualization($matches["format"])){
            RequestLogger::logRequest($package,$resourcename,$parameters);
        }
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
        if(!$this->isVisualization($matches["format"])){
            RequestLogger::logRequest($package,$resourcename,$parameters);
        }
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

    // visualizations may not be logged
    private function isVisualization($format){
        $vis = array("map","grid","bar","chart","column","pie");
        return in_array($format,$vis);
    }
}
?>
