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
        
        $model = ResourcesModel::getInstance();
        $doc = $model->getAllDoc();
        
        if (isset($package) && isset($resourcename)){
            if(isset($doc->$package) && isset($doc->$package->$resourcename) 
                                     && !$this->isAuthenticated($package,$resourcename)) {
                header('WWW-Authenticate: Basic realm="' . Config::$HOSTNAME . Config::$SUBDIR . '"');
                header('HTTP/1.0 403 Forbidden');
                exit();
            }
        }else{
            throw new ResourceOrPackageNotFoundTDTException("You have to pass along a resource behind your package.");
        }

        /**
         * If there is only a package passed, pass along a list of its resources
         * NOTE: for this branch a package and a resource must be passed ! So the part where package list their 
         * resource might become obsolete.
         */
        

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
        RequestLogger::logRequest($package,$resourcename,$parameters);
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
        RequestLogger::logRequest($package,$resourcename,$parameters);
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

    private function isAuthenticated($package,$resource) {

        return TRUE;

        if(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])){
            return $_SERVER['PHP_AUTH_USER'] == Config::$API_USER && $_SERVER['PHP_AUTH_PW'] == Config::$API_PASSWD;
        }

        if(isset($_GET["key"])){
            /**
             * Get the resource_ids that are connected with the key
             * 1) Get the key from url
             * 2) Look it up in the api_key table
             * 3) Get the id from the api_key entry
             * 4) Look up the id in the access_list table, get all of the resource_id's
             * 5) Get the Resource id from the requested resource and look for matches from result 4)
             * 6) return false or true according to step 5)
             */
            $model = ResourcesModel::getInstance();
            $key = $_GET["key"];
            $apiId = $model->getAPIId($key);
            $access_granted = $model->isKeyAuthorized($apiId, $package,$resource);
            return $access_granted;
        }
        return FALSE;
    }
}
?>
