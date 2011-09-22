<?php
/**
 * Class for reading(fetching) a remote resource
 *
 * @package The-Datatank/model/resources/read
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */

include_once("model/IReader.php");
include_once("model/DBQueries.class.php");

class RemoteResourceReader extends AReader{

    private $remoteResource;

    public function __construct($package,$resource){
        parent::__construct($package,$resource);
        $this->remoteResource       = $this->fetchResource();
        $this->requiredParameters = $this->remoteResource->reqparams;
        $this->parameters         = $this->remoteResource->parameters;
        // TODO optional parameters ?!
    }
    
    /**
     * execution method
     */
    public function read(){
        return new RemoteResource($this->remoteResource->remote_package, $resource,
                                  $this->remoteResource->reqparams,
                                  $this->remoteResource->base_url);
    }
    

    private function fetchResource(){

        $result = DBQueries::getRemoteResource($this->package, $this->resource);
        if(sizeof($result) == 0){
            throw new ResourceOrPackageNotFoundTDTException("Cannot find the remote resource with package and resource pair as: ".$package."/".$resource);
        }
        $url = $result["url"]."TDTInfo/Resources/".$result["package"]."/".$result["resource"].".php";
        $options = array("cache-time" => 1); //cache for 1 hour
        $request = TDT::HttpRequest($url, $options);
        if(isset($request->error)){
            throw new HttpOutTDTException($url);
        }
        $data = unserialize($request->data);
        $this->currentRemoteResource = new stdClass();
        $this->currentRemoteResource->package = $package;
        $this->currentRemoteResource->remote_package = $result["package"];
        $this->currentRemoteResource->doc = $data["doc"];
        $this->currentRemoteResource->resource = $resource;
        $this->currentRemoteResource->formats = $data["formats"];
        $this->currentRemoteResource->base_url = $result["url"];
        $this->currentRemoteResource->parameters = $data["parameters"];
        $this->currentRemoteResource->reqparams = $data["requiredparameters"];
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