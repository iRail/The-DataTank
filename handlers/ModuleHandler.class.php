<?php
  /**
   * The module handler will look for GET and POST requests on a certain module. It will ask the factories to return the right Resource instance.
   * If it checked all required parameters, checked the format, it will perform the call and get a result. This result is printer by a printer returned from the PrinterFactory
   *
   * @package The-Datatank/handlers
   * @copyright (C) 2011 by iRail vzw/asbl
   * @license AGPLv3
   * @author Pieter Colpaert
   * @author Jan Vansteenlandt
   */
include_once('printer/PrinterFactory.php');
include_once('handlers/RequestLogger.class.php');
include_once('factories/FilterFactory.class.php');
include_once('resources/GenericResource.class.php');

class ModuleHandler {

    private $printerfactory;

    function GET($matches) {

	//always required: a module and a resource. This will always be given since the regex should be matched.
	$module = $matches['module'];
	$resourcename = $matches['resource'];

	//This will create an instance of a factory depending on which format is set
	$this->printerfactory = PrinterFactory::getInstance();
	
	//This will create an instance of AResource
	$factory= AllResourceFactory::getInstance();
	$resource = $factory->getResource($module,$resourcename);
	
	// Jan: I have not found a decent reason (actually no reason for that matter) to provide
	// parameters for genericresources. All the parameters are generic and are to be set
	// when constructing the generic resource for the first time in the database as call parameters...
	// So that's why i did'nt implement any getRequiredParameters() for a GenericResource(yet)

	$RESTparameters = array();
	if(isset($matches['RESTparameters'])){
	    $RESTparameters = explode("/",$matches['RESTparameters']);
	    array_pop($RESTparameters); // remove the last element because that just contains the GET parameters
	}

	 $requiredparams = array();
	if(!$resource instanceof GenericResource){
	    
	    // First up: check the required parameters - TODO: what if a param hasn't been given?
	    // The required parameters are at the beginning of our url, right after the resourcename
	    //for logging purposes: requiredparameter values
	   
	

	    foreach($resource->getRequiredParameters() as $parameter){
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
	}
	
	// check if the given format is allowed by the method
	$printmethod = "";
	foreach($resource->getAllowedPrintMethods() as $printername){
	    if(strtolower($this->printerfactory->getFormat()) == strtolower($printername)){
		$printmethod = $printername;
		break;//I have sinned again
	    }
	}

	//if the printmethod is not allowed, just throw an exception
	if($printmethod == ""){
	    throw new FormatNotAllowedTDTException($this->printerfactory->getFormat(),$resource->getAllowedPrintMethods());
	}

	//Let's do the call!
	$result = $resource->call();

	// for logging purposes
	$subresources = array();
	$filterfactory = FilterFactory::getInstance();
	// apply RESTFilter
	if(sizeof($RESTparameters)>0){
	    
	    $RESTFilter = $filterfactory->getFilter("RESTFilter",$RESTparameters);
	    $resultset = $RESTFilter->filter($result);
	    $subresources = $resultset->subresources;
	    $result = $resultset->result;
	}
	
	//Apply Lookup filter if asked
	
	if(isset($_GET["filterBy"]) && isset($_GET["filterValue"])){
	    if(!is_array($result)){
		throw new FilterTDTException("The object provided is not a collection."); 
	    }else{
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
	    $RESTresource = $RESTparameters[sizeof($RESTparameters)-1];
	    $o->$RESTresource = $result;
	    $result = $o;
	}

	
	// Log our succesful request
	RequestLogger::logRequest($matches,$requiredparams,$subresources);
	
	$printer = $this->printerfactory->getPrinter(strtolower($resourcename), $result);
	$printer->printAll();
	//this is it!
    }
}
//--- BEWARE: this file should be less than 100 lines of code!
//42
?>