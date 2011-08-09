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
require_once('printer/PrinterFactory.php');
require_once('handlers/RequestLogger.class.php');
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
	
	// First up: check the required parameters - TODO: what if a param hasn't been given?

	// The require parameters are at the beginning of our url, right after the resourcename
	$RESTparameters = array();
	if(isset($matches['RESTparameters'])){
	    $RESTparameters = explode("/",$matches['RESTparameters']);
	    array_pop($RESTparameters); // remove the last element because that just contains the GET parameters
	}
	
	foreach($resource->getRequiredParameters() as $parameter){
	    //set the parameter of the method
	    if(!isset($RESTparameters[0])){
		throw new ParameterTDTException($parameter);
	    }
	    $resource->setParameter($parameter, $RESTparameters[0]);
	    
	    //removes the first element and reindex the array
	    array_shift($RESTparameters);
	}
	//what remains in the $resources array are specification for a RESTful way of identifying objectparts
	//for instance: http://api.../TDTInfo/Modules/module/1/ would make someone only select the second module

	//also give the non REST parameters to the resource class
	$resource->processParameters();

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

	//Support RESTful URI lookups
	foreach($RESTparameters as $resource){
	    if(is_object($result) && isset($result->$resource)){
		$result = $result->$resource;
	    }elseif(is_array($result) && isset($result[$resource])){
		$result = $result[$resource];
	    }else{
		break;//on error, just return what we have so far
	    }
	    array_push($subresources,$resource);
	}
	if(!is_object($result)){
	    $o = new stdClass();
	    $RESTresource = $RESTparameters[sizeof($RESTparameters)-1];
	    $o->$RESTresource = $result;
	    $result = $o;
	}

	$requiredparams = $factory->getResourceRequiredParameters($module,$resourcename);
	
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