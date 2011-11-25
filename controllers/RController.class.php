<?php
/**
 * The controller will look for GET and POST requests on a certain module. It will ask the factories to return the correct Resource instance.
 * If it checked all required parameters, checked the format, it will perform the call and get a result. This result is a printer returned from the PrinterFactory
 *
 * @package The-Datatank/controllers
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert
 * @author Jan Vansteenlandt
 */
include_once('formatters/FormatterFactory.class.php');
include_once('aspects/logging/RequestLogger.class.php');
include_once('model/filters/FilterFactory.class.php');
include_once('model/ResourcesModel.class.php');
include_once("model/DBQueries.class.php");

class RController extends AController {

    private $formatterfactory;

    function GET($matches) {
        //always required: a package and a resource. This will always be given since the regex should be matched.
        $package = trim($matches['package']);
        $resourcename = trim($matches['resource']);
        //This will create an instance of a factory depending on which format is set
        $this->formatterfactory = FormatterFactory::getInstance($matches["format"]);

        //Get an instance of our model
        $model = ResourcesModel::getInstance();
        //ask the model for our documentation: access to all packages and resources!
        $doc = $model->getAllDoc();

        if(!isset($doc->$package) || !isset($doc->$package->$resourcename)){
            throw new ResourceOrPackageNotFoundTDTException("please check if $package and $resourcename are a correct package-resource pair");
        }        

        $RESTparameters = array();
        if (isset($matches['RESTparameters']) && $matches['RESTparameters'] != "") {
            $RESTparameters = explode("/", rtrim($matches['RESTparameters'], "/"));
        }

        $parameters = $_GET;


        //check for required parameters
        foreach ($doc->$package->$resourcename->requiredparameters as $parameter) {
            //set the parameter of the method
            
            if (!isset($RESTparameters[0])) {
                throw new ParameterTDTException($parameter);
            }
            $parameters[$parameter] = $RESTparameters[0];
            //removes the first element and reindex the array - this way we'll only keep the object specifiers in this array
            array_shift($RESTparameters);
        }
        //what remains in the $resources array are specification for a RESTful way of identifying objectparts
        //for instance: http://api.../TDTInfo/Modules/module/1/ would make someone only select the second module

        $result = $model->readResource($package, $resourcename, $parameters, $RESTparameters);
        
        //maybe the resource reinitialised the database, so let's set it up again with our config, just to be sure.
        R::setup(Config::$DB, Config::$DB_USER, Config::$DB_PASSWORD);

        /*
         * Add foreign the required foreign relations URL's to the resulting object
         * If you do not know what these are, check our wiki on github.
         */

        $for_rel_urls = DBQueries::getForeignRelations($package, $resourcename);

        /*
         * If there are foreign relations between resources, then add them to the resulting object
         */
        /*
        if (!empty($for_rel_urls)) {
            foreach ($result->$resourcename as $key => $item) {
                $properties = get_object_vars($item);
                foreach ($properties as $property => $value) {
                    if (array_key_exists($property, $for_rel_urls)) {
                        /*
                         * the property now contains a field that has to be matched, so we we add it
                         * to the end of our RESTful URL i.e. the address field of a person object is 
                         * a FK with id = 5 then my object person->address = 5 will be translated to 
                         * person->address = myhost/mypackage/addresslist/object/?filterBy=FK&filterValue=5
                         */
        /*$result->{$resourcename}[$key]->$property = $for_rel_urls[$property] . $value;
                    }
                }
            }
            }*/

        // apply RESTFilter
        $subresources = array();
        $filterfactory = FilterFactory::getInstance();

        if (sizeof($RESTparameters) > 0) {
            //Miel: When the result is an ontology, the REST filtering is handled different
            //It's the RAP API who will do the filtering on the database
            if (!(is_subclass_of($result, 'Model') || is_a($result, 'Model'))) {
                $RESTFilter = $filterfactory->getFilter("RESTFilter", $RESTparameters);
                $resultset = $RESTFilter->filter($result);
                $subresources = $resultset->subresources;
                $result = $resultset->result;
            }
        }
        // Apply Lookup filter if asked, this has been implemented according to the 
        // open search specifications

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
        
        $printer = $this->formatterfactory->getPrinter(strtolower($resourcename), $result);
        $printer->printAll();

        //only log the request if this is not a remote resource
        if (!isset($doc->$package->$resourcename->base_url)) {
            RequestLogger::logRequest();
        }
        
        /**
         * check for updates if necessary
         */
        if( !$this->is_update_process_running()){
            $this->run_update_in_background();
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

    private function run_update_in_background(){
        passthru("php bin/cache\ update/update_cache.php >/dev/null 2>&1 &");
    }

    private function is_update_process_running(){
        exec("ps -C 'php bin/cache\ update/update_cache.php >/dev/null 2>&1 &'",$response );
        return(count($response) >= 2);
    }
}
?>
