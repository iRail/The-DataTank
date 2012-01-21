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
include_once("model/resources/AResource.class.php");

class RemoteResourceFactory extends AResourceFactory{

    public function hasResource($package,$resource){
	$rn = $this->getAllResourceNames();
        return isset($rn[$package]) && in_array($resource, $rn[$package]);
    }

    protected function getAllResourceNames(){
        $resultset = DBQueries::getAllRemoteResourceNames();        
        $resources = array();
        foreach($resultset as $result){
            if(!isset($resources[$result["package_name"]])){
                $resources[$result["package_name"]] = array();
            }
            $resources[$result["package_name"]][] = $result["res_name"];
        }        
        return $resources;
    }

    public function createCreator($package,$resource, $parameters, $RESTparameters){
        include_once("model/resources/create/RemoteResourceCreator.class.php");
        $creator = new RemoteResourceCreator($package,$resource, $RESTparameters);
        foreach($parameters as $key => $value){
            $creator->setParameter($key,$value);
        }
        return $creator;
    }
    
    public function createReader($package,$resource, $parameters, $RESTparameters){
        include_once("model/resources/read/RemoteResourceReader.class.php");
        $reader = new RemoteResourceReader($package, $resource, $RESTparameters, $this->fetchResourceDocumentation($package,$resource));
        $reader->processParameters($parameters);
        return $reader;
    }
    
    
    public function createDeleter($package,$resource, $RESTparameters){
        include_once("model/resources/delete/RemoteResourceDeleter.class.php");
        return new RemoteResourceDeleter($package,$resource, $RESTparameters);
    }
    
    public function makeDoc($doc){
        foreach($this->getAllResourceNames() as $package => $resourcenames){
            if(!isset($doc->$package)){
                $doc->$package = new StdClass();
//move this to another resource!
//                $doc->$package->creation_date = DBQueries::getPackageCreationTime($package);
            }
            foreach($resourcenames as $resource){
                $doc->$package->$resource = new StdClass();
                $doc->$package->$resource = $this->fetchResourceDocumentation($package, $resource);
            }
        }
    }

    public function makeDeleteDoc($doc){
        //add stuff to the delete attribute in doc. No other parameters expected
        foreach($this->getAllResourceNames() as $package => $v){
            foreach($v as $resource){
                $d = new stdClass();
                $d->doc = "Delete this remote resource by calling the URI given in this object with a HTTP DELETE method";
                $d->uri = Config::$HOSTNAME . Config::$SUBDIR . $package . "/" . $resource;
                $doc->delete[] = $d;
            }
        }
    }
    
    public function makeCreateDoc($doc){
        //add stuff to create attribute in doc. No other parameters expected
        $d = new stdClass();
        $d->doc = "Creates a new remote resource by executing a HTTP PUT on an URL formatted like " . Config::$HOSTNAME . Config::$SUBDIR . "packagename/newresource. The base_uri needs to point to another The DataTank instance.";
        include_once("model/resources/create/RemoteResourceCreator.class.php");
        $resource = new RemoteResourceCreator("","", array());//make an empty object. In the end we only need a remote resource
        $d->parameters = $resource->documentParameters();
        $d->requiredparameters = $resource->documentRequiredParameters();
        if(!isset($doc->create)){
            $doc->create =new stdClass();
        }
        $doc->create->remote = $d;
    }

    /*
     * This object contains all the information 
     * FROM the last used
     * requested object. This way we wont have to call the remote resource
     * every single call to this factory. If we receive a call
     * for another resource, we replace it by the newly asked factory.
     */
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
        $remoteResource = new stdClass();
        $remoteResource->package = $package;
        $remoteResource->remote_package = $result["package"];
        if(isset($remoteResource->doc) && isset($data[$resource])){
            $remoteResource->doc = $data[$resource]->doc;
        }else{
            $remoteResource->doc = new stdClass();
        }
        
        
        $remoteResource->resource = $resource;
        $remoteResource->base_url = $result["url"];
        if(isset($data[$resource]->parameters)){
            $remoteResource->parameters = $data[$resource]->parameters;
        }
        if(isset($data[$resource]->requiredparameters)){
            $remoteResource->requiredparameters = $data[$resource]->requiredparameters;
        }
        return $remoteResource;
    }
    
}

?>
