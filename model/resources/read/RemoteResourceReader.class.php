<?php
/**
 * Class for reading(fetching) a remote resource
 *
 * @package The-Datatank/model/resources/read
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */
include_once("model/DBQueries.class.php");

class RemoteResourceReader extends AReader{

    private $remoteResource;

    public function __construct($package,$resource){
        parent::__construct($package,$resource);
        $this->fetchResource();
        
    }
    
    /**
     * execution method
     */
    public function read(){
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

    private function fetchResource(){
        $result = DBQueries::getRemoteResource($this->package, $this->resource);
        if(sizeof($result) == 0){
            throw new ResourceOrPackageNotFoundTDTException("Cannot find the remote resource with package and resource pair as: ".$package."/".$resource);
        }
        $url = $result["url"]."TDTInfo/Resources/".$result["package"]."/".$result["resource"].".php";
        $options = array("cache-time" => 1); //cache for 1 second
        $request = TDT::HttpRequest($url, $options);
        if(isset($request->error)){
            throw new HttpOutTDTException($url);
        }
        $data = unserialize($request->data);
        $this->remoteResource = new stdClass();
        $this->remoteResource->package = $package;
        $this->remoteResource->remote_package = $result["package"];
        $this->remoteResource->doc = $data["doc"];
        $this->remoteResource->resource = $resource;
        $this->remoteResource->formats = $data["formats"];
        $this->remoteResource->base_url = $result["url"];
        $this->remoteResource->parameters = $data["parameters"];
        $this->remoteResource->requiredparameters = $data["requiredparameters"];
    }
    
    protected function setParameter($name,$val){
        //add the parameters to $this
	$this->$name = $val;
    }

    /**
     * get the documentation about getting of a resource
     * @return String with some documentation about the resource
     */
    public function getReadDocumentation(){
        return $this->remoteResource->doc;
    }    

    /**
     * get the allowed formats
     * @return Array with all of the allowed formatter names
     */
    public function getAllowedFormatters(){
        return $this->remoteResource->formats;
    }
}
?>