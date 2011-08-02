<?php
/**
 * The module handler will look for GET and POST requests on a certain module. It will ask the factories to return the right Resource instance.
 * If it checked all required parameters, checked the format, it will perform the call and get a result. This result is printer by a printer returned from the PrinterFactory
 *
 * @package The-Datatank/handlers
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert
 */
require_once('printer/PrinterFactory.php');
require_once('handlers/RequestLogger.class.php');

class ModuleHandler {

    private $printerfactory;

    function GET($matches) {

	RequestLogger::logRequest();

	//always required: a module and a resource. This will always be given since the regex should be matched.
	$module = $matches['module'];
	$resourcename = $matches['resource'];

	$this->printerfactory = new PrinterFactory();
	
	//Now it's time to make us some factories. But what kind of factory do we need? 
	//The only way to find out is to create an instance of each factory and try to get an object of a resource
	$factories = array(); //(ordening does matter here! Put the least expensive method on top)
	$factories[] = new GenericResourceFactory($module, $resourcename);
	$factories[] = new InstalledResourceFactory($module,$resourcename);
	$factories[] = new RemoteResourceFactory($module, $resourcename);

	//find the one who has the resource!
	$resource = NULL;
	foreach($factories as $factory){
	    if($factory->hasResource()){
		$resource = $factory->getResource();
		break; //I know I have sinned
	    }
	}

	//if not really any factory has the resource, throw an exception
	if(is_null($resource)){
	    throw new MethodOrModuleNotFoundTDTException($module . "/" .$resourcename);
	}

	// It's official, we have a resource object!
	// Let's populate it!
	
	// First up: check the required parameters - TODO: what if a param hasn't been given?

	// The require parameters are at the beginning of our url, right after the resourcename
	$RESTparameters = array();
	if(isset($matches['RESTparameters'])){
	    $RESTparameters = explode("/",$matches['RESTparameters']);
	    array_pop($RESTparameters); // remove the last elemenet because that just contains the GET parameters
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
	    throw new FormatNotAllowedTDTException($this->format,$resource->getAllowedPrintMethods());
	}

	//Let's do the call!
	$result = $resource->call();
	
	//Support RESTful URI lookups
	foreach($RESTparameters as $resource){
	    if(is_object($result) && isset($result->$resource)){
		$result = $result->$resource;
	    }elseif(is_array($result) && isset($result[$resource])){
		$result = $result[$resource];
	    }else{
		break;//on error, just return what we have so far
	    }
	}
	if(!is_object($result)){
	    $o = new stdClass();
	    $resource = $resources[sizeof($resources)-1];
	    $o->$resource = $result;
	    $result = $o;
	}
 
	$printer = $this->printerfactory->getPrinter(strtolower($resourcename), $result);
	$printer->printAll();
	//this is it!
    }
}
?>