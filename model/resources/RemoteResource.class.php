<?php
/**
 * This will fetch data from another datatank. 
 *
 * @package The-Datatank/resources
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 * @author Pieter Colpaert
 */

class RemoteResource extends AResource {

    private $resource, $package;
    private $optionalparams;
    private $requiredparams;
    private $base_url;
    private $requiredparametervalues;
    
    public function __construct($remotepackage, $remoteresource,$reqparams = array(),$base_url){
        // we don't need to pass along the requiredparameters
        // because we'll pass them along the url, just as they were entered
        // in the given url.
        $this->package = $remotepackage;
        $this->resource = $remoteresource;
        $this->optionalparams = array();
        $this->base_url = $base_url;
        $this->requiredparams = $reqparams;
        $this->requiredparametervalues = array(); 
    }

    public function call(){
        
	//extract the right parameters and concatenate them to create the right URL
	$params = "?";
	foreach($this->optionalparams as $key => $val){
	    $params .= $key . "=" . urlencode($val) . "&";
	}
	$params = rtrim($params, "&");

	//the url consists of the baseurl (this has a trailing slash and contains the subdir) - the resource is a specifier in the baseurl
	//params is a url containing the possible 
	$url = $this->base_url . $this->package . "/".$this->resource . "/";
        foreach($this->requiredparametervalues as $param){
            $url = $url . $param."/";
        }
        $url= rtrim($url, "/");
        //add format: php because we're going to deserialize this
        $url .= ".php";
        
        $url = $url . $params;

	//Request the remote server and check for errors. If no error, unserialize the data
	$options = array("cache-time" => 0, "headers" => array("User-Agent" => isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:""));
	$request = TDT::HttpRequest($url, $options);
	if(isset($request->error)){
	    throw new RemoteServerTDTException($request->data);
	}

	//unserialize the data of the request and return it!
	return unserialize($request->data);
    }

    public function setParameter($name,$val){
	if(!in_array($name,$this->requiredparams)){
            $this->optionalparams[$name] = $val;
        }else{
            $this->requiredparametervalues[] = $val;
        }
    }
}

?>