<?php
/**
 * This class will handle a remote resource and connect to another DataTank instance for their data
 *
 * @package The-Datatank/model
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 * @author Pieter Colpaert
 */
include_once("model/resources/RemoteResource.class.php");

class RemoteResourceFactory extends AResourceFactory{
    
    public function __construct(){
        
    }

    public function createCreator($package,$resource, $parameters){
        
    }
    
    public function createReader($package,$resource, $parameters){
        
    }
    
    public function createUpdater($package,$resource, $parameters){
        
    }
    
    public function createDeleter($package,$resource){
        
    }
    
    public function makeDoc($doc){
        
        foreach($this->getAllResourceNames() as $package => $resourcenames){
            foreach($resourcenames as $resource){
                $this->fetchResourceDocumentation($package, $resource);
                
            }
        }
    }

    /*
     * This object contains all the information 
     * FROM the last used
     * requested object. This way we wont have to call the remote resource
     * every single call to this factory. If we receive a call
     * for another resource, we replace it by the newly asked factory.
     */
    private $currentRemoteResource;

    private function fetchResourceDocumentation($package,$resource){
        $result = DBQueries::getRemoteResource($package, $resource);
        if(sizeof($result) == 0){
            throw new ResourceOrPackageNotFoundTDTException("Cannot find the remote resource with package and resource pair as: ".$package."/".$resource);
        }
        $url = $result["url"]."TDTInfo/Resources/".$result["package"]."/".$result["resource"].".php";
        $options = array("cache-time" => 5); //cache for 5 seconds
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
        $this->currentRemoteResource->requiredparameters = $data["requiredparameters"];
    }
    
}

?>
