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

class RController extends AController{

    private $formatterfactory;
    
    function GET($matches) {
	//always required: a package and a resource. This will always be given since the regex should be matched.
	$package = $matches['package'];
	$resourcename = $matches['resource'];

	//This will create an instance of a factory depending on which format is set
	$this->formatterfactory = FormatterFactory::getInstance($matches["format"]);
        $this->formatterfactory->setPackage($package);
        $this->formatterfactory->setResource($resourcename);

	//This will create an instance of AResource
	$model = ResourcesModel::getInstance();
	$resource = $model->getResource($package,$resourcename);

	$RESTparameters = array();
	if(isset($matches['RESTparameters']) && $matches['RESTparameters'] != ""){
	    $RESTparameters = explode("/",rtrim($matches['RESTparameters'],"/"));
	}
        
        $requiredparams = array();

        foreach($model->getResourceRequiredParameters($package,$resourcename) as $parameter){
            //set the parameter of the method
            if(!isset($RESTparameters[0])){
                throw new ParameterTDTException($parameter);
            }
            $resource->setParameter($parameter, $RESTparameters[0]);
            $requiredparams[$parameter]=$RESTparameters[0];
	    
            //removes the first element and reindex the array
            array_shift($RESTparameters);
        }
        //what remains in the $resources array are specification for a RESTful way of identifying objectparts
        //for instance: http://api.../TDTInfo/Modules/module/1/ would make someone only select the second module

        //also give the non REST parameters to the resource class
        $resource->processParameters();
	
        // check if the given format is allowed by the method
        $printmethod = "";
        foreach($model->getAllowedPrintMethods($package,$resourcename) as $printername){
            if(strtolower($this->formatterfactory->getFormat()) == strtolower($printername)){
                $printmethod = $printername;
                break;
            }
        }

        //if the printmethod is not allowed, just throw an exception
        if($printmethod == "" || strtolower($this->formatterfactory->getFormat()) == "about"){
            throw new FormatNotAllowedTDTException($this->formatterfactory->getFormat(),$resource->getAllowedPrintMethods());
        }

        //Let's do the call!
        $result = $resource->call();

        /*
         * Add foreign the required foreign relations URL's to the resulting object
         * If you do not know what these are, check our wiki on github.
         */
        
        $for_rel_urls = DBQueries::getForeignRelations($package,$resourcename);
 
        /*
         * If there are foreign relations between resources, then add them to the resulting object
         */
        if(!empty($for_rel_urls)){
            foreach($result->$resourcename as $key => $item){
                $properties = get_object_vars($item);
                foreach($properties as $property => $value){
                    if(array_key_exists($property,$for_rel_urls)){
                        /* 
                         * the property now contains a field that has to be matched, so we we add it
                         * to the end of our RESTful URL i.e. the address field of a person object is 
                         * a FK with id = 5 then my object person->address = 5 will be translated to 
                         * person->address = myhost/mypackage/addresslist/object/?filterBy=FK&filterValue=5
                         */
                        $result->{$resourcename}[$key]->$property = $for_rel_urls[$property].$value;
                    }
                }
            }
        }
        
        // apply RESTFilter
        $subresources = array();
        $filterfactory = FilterFactory::getInstance();
        
        if(sizeof($RESTparameters)>0){
            $RESTFilter = $filterfactory->getFilter("RESTFilter",$RESTparameters);
            $resultset = $RESTFilter->filter($result);
            $subresources = $resultset->subresources;
            $result = $resultset->result;
        }
	
        // Apply Lookup filter if asked, this has been implemented according to the 
        // open search specifications
	
        if(isset($_GET["filterBy"]) && isset($_GET["filterValue"])){
            if(is_array($result)){
                $filterparameters = array();
                $filterparameters["filterBy"] = $_GET["filterBy"];
                $filterparameters["filterValue"] = $_GET["filterValue"];
                if(isset($_GET["filterOp"])){
                    $filterparameters["filterOp"] = $_GET["filterOp"];
                }
		
                $searchFilter = $filterfactory->getFilter("SearchFilter",$filterparameters);
                $result = $searchFilter->filter($result);
            }	    
        }
	
        if(!is_object($result)){
            $o = new stdClass();
            $RESTresource = "";
            if(sizeof($RESTparameters)>0){
                $RESTresource = $RESTparameters[sizeof($RESTparameters)-1];
            }else{
                $RESTresource = $resourcename;
            }
            
            $o->$RESTresource = $result;
            $result = $o;
        }
	
        $printer = $this->formatterfactory->getPrinter(strtolower($resourcename), $result);
        $printer->printAll();
        if($model->getResourceType($package,$resourcename) != "remote"){
            RequestLogger::logRequest($package,$resourcename, $requiredparams, $subresources);            
        }
    }

    /**
     * You cannot PUT on a representation
     */
    function PUT($matches){
        throw new RepresentationCUDCallTDTException();
    }
 
    /**
     * You cannot delete a representation
     */
    public function DELETE($matches){
        throw new RepresentationCUDCallTDTException();
    }

    /**
     * You cannot use post on a representation
     */
    public function POST($matches){
        throw new RepresentationCUDCallTDTException();
    }
    
}

?>
