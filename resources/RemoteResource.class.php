<?php
/**
 * This will fetch data from another datatank. 
 *
 * @package The-Datatank/resources
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert
 */

class RemoteResource extends AResource {

    private $baseurl, $resource, $module;
    private $docs;
    
    public function __construct($baseurl, $remotemodule, $remoteresource){
	$this->baseurl = $baseurl;
	$this->module = $remotemodule;
	$this->resource = $remoteresource;
	//get documentation object from remote
	$formaturl = $baseurl . "/TDTInfo/Module/" . $remotemodule . "/". $remoteresource."/?format=php";
	$request= TDT::HttpRequest($formaturl);
	if(isset($request->error)){
	    throw new RemoteServerTDTException($request->data);
	}
	$this->docs = unserialize($request->data);
    }

    public function getRequiredParameters(){
	return $this->docs->requiredparameter;
    }

    public function getParameters(){
	return $this->docs->parameter;
    }
     
    public function getAllowedPrintMethods(){
	return $this->docs->allowedprintmethod;
    }

    public function getDoc(){
	return $this->docs->doc;
    }

    public function call(){
	//extract the right parameters and concatenate them to create the right URL
	$params = "?";
	foreach($this->params as $key => $val){
	    $params .= $key . "=" . url_encode($val) . "&";
	}
	//We need php output to unserialize it afterwards
	$params .= "format=php";

	//the url consists of the baseurl (this has a trailing slash and contains the subdir) - the resource is a specifier in the baseurl
	//params is a url containing the possible 
	$url = $this->baseurl . $this->resource . "/" . $params;
	
	//Request the remote server and check for errors. If no error, unserialize the data
	$request = TDT::HttpRequest($url."format=php");	  
	if(isset($request->error)){
	    throw new RemoteServerTDTException($request->data);
	}

	//unserialize the data of the request and return it!
	return unserialize($request->data);
    }

    public function setParameter($name,$val){
	//just set every allowed parameter that comes in. No format-processing
	$this->params[] = $val;
    }

}

?>