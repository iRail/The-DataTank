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
    
    /*
     * This object contains all the information 
     * FROM the last used
     * requested object. This way we wont have to call the remote resource
     * every single call to this factory. If we receive a call
     * for another resource, we replace it by the newly asked factory.
     */
    private $currentRemoteResource;
    
    public function __construct(){
        $this->currentRemoteResource = new stdClass();
        $this->currentRemoteResource->package = "";
        $this->currentRemoteResource->resource = "";
    }
    
    /**
     * @return returns a string containing the documentation about the resource. It returns an empty string when the resource could not be found
     */
    public function getResourceDoc($package, $resource){
        if( $this->currentRemoteResource->package != $package || $this->currentRemoteResource->resource != $resource){
            $this->fetchResource($package,$resource);
        }
        
        return $this->currentRemoteResource->data["doc"];
    }

    /**
     * @return returns an associative array with the documentation for each parameter for a specific resource 
     */
    public function getResourceParameters($package, $resource){
        if( $this->currentRemoteResource->package != $package || $this->currentRemoteResource->resource != $resource){
            $this->fetchResource($package,$resource);
        }
        return $this->currentRemoteResource->data["parameters"];
        
    }    

    /**
     * @return returns an array with all required parameters
     */
    public function getResourceRequiredParameters($package,$resource){
        if( $this->currentRemoteResource->package != $package || $this->currentRemoteResource->resource != $resource){
            $this->fetchResource($package,$resource);
        }
        return $this->currentRemoteResource->data["requiredparameters"];
    }

    public function getAllowedPrintMethods($package,$resource){
        if( $this->currentRemoteResource->package != $package || $this->currentRemoteResource->resource != $resource){
            $this->fetchResource($package,$resource);
        }
        return $this->currentRemoteResource->data["formats"];
    }
    

    public function hasResource($package,$resource){
        $rn = $this->getAllResourceNames();
        if(isset($rn[$package])){ 
            return in_array($resource, $rn[$package]);
        }
        return false;
    }

    /**
     * @return an array containing all the remote resourcenames available
     */
    public function getAllResourceNames(){
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
    
    /**
     * @return gets an instance of a AResource class.
     */
    public function getResource($package,$resource){
        if( $this->currentRemoteResource->package != $package || $this->currentRemoteResource->resource != $resource){
            $this->fetchResource($package,$resource);
        }
        return new RemoteResource($this->currentRemoteResource->remote_package, $resource,
                                  $this->currentRemoteResource->reqparams,
                                  $this->currentRemoteResource->base_url);
    }

    private function fetchResource($package,$resource){
	    $result = DBQueries::getRemoteResource($package, $resource);
	    
        if(sizeof($result) == 0){
            throw new ResourceOrPackageNotFoundTDTException("Cannot find the remote resource with package and resource pair as: ".$package."/".$resource);
        }else{
            $url = $result["url"]."TDTInfo/Resources/".$result["package"]."/".$result["resource"]."/?format=php";
        }
        $options = array("cache-time" => 3600); //cache for 1 hour
        $request = TDT::HttpRequest($url, $options);
        $data = unserialize($request->data);
        $this->currentRemoteResource = new stdClass();
        $this->currentRemoteResource->package = $package;
        $this->currentRemoteResource->remote_package = $result["package"];
        $this->currentRemoteResource->resource = $resource;
        $this->currentRemoteResource->data = $data;
        $this->currentRemoteResource->base_url = $result["url"];
        $this->currentRemoteResource->parameter_keys = array_keys($data["parameters"]);
        $this->currentRemoteResource->reqparams = $data["requiredparameters"];
    }
    
    /**************************************************SETTERS*********************************/

    public function deletePackage($package){
        DBQueries::deleteRemotePackage($package);
    }

    public function deleteResource($package, $resource){
        DBQueries::deleteRemoteResource($package, $resource);
    }

    public function addResource($package,$resource, $content){
        //insert a row with the right URI to the package/resource
        $model = ResourcesModel::getInstance();
        $resource_id = $model->getResourceId($package, $resource);

        $base_url = $content["url"];
        // make sure te base_url ends with a /
        if(substr(strrev($base_url),0,1) != "/"){
            $base_url .= "/";
        }
        return DBQueries::storeRemoteResource($resource_id, $content["package_name"], $base_url);
    }

    public function updateResource($package,$resource,$content){
        //update a URI to a resource
    }

}

?>
