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
     * This object contains all the information from the last used
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
         R::setup(Config::$DB,Config::$DB_USER,Config::$DB_PASSWORD);

	$resultset = R::getAll(
            "select resource_name as resource, module.module_name as module from remote_resource,module
             where module.id = remote_resource.module_id"
	);
        $resources = array();
        foreach($resultset as $result){
            if(!isset($resources[$result["module"]])){
                $resources[$result["module"]] = array();
            }
            $resources[$result["module"]][] = $result["resource"];
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
        
        R::setup(Config::$DB,Config::$DB_USER,Config::$DB_PASSWORD);
	$param = array(':module' => $package, ':resource' => $resource);
	$result = R::getAll(
	    "select rem_rec.base_url as url ,rem_rec.module_name as module,rem_rec.resource_name as resource
             from module,remote_resource as rem_rec
             where module.module_name=:module and rem_rec.resource_name =:resource
             and module.id=rem_rec.module_id",
	    $param
	);
        if(sizeof($result) == 0){
            throw new ResourceOrPackageNotFoundTDTException("Cannot find the remote resource with package and resource pair as: ".$package."/".$resource);
        }else{
            $url = $result[0]["url"]."TDTInfo/Resources/".$result[0]["module"]."/".$result[0]["resource"]."/?format=php";
        }
        $options = array("cache-time" => 3600); //cache for 1 hour
        $request = TDT::HttpRequest($url, $options);
        $data = unserialize($request->data);
        $this->currentRemoteResource = new stdClass();
        $this->currentRemoteResource->package = $package;
        $this->currentRemoteResource->remote_package = $result[0]["package"];
        $this->currentRemoteResource->resource = $resource;
        $this->currentRemoteResource->data = $data;
        $this->currentRemoteResource->base_url = $result[0]["url"];
        $this->currentRemoteResource->parameter_keys = array_keys($data["parameters"]);
        $this->currentRemoteResource->reqparams = $data["requiredparameters"];
    }
    
    /**************************************************SETTERS*********************************/

    public function deletePackage($package){
        $deleteRemoteResource = R::exec(
            "DELETE FROM remote_resource 
                 WHERE module_id IN (SELECT id FROM module WHERE module_name=:module)",
            array(":module" => $package)
        );
    }

    public function deleteResource($package, $resource){
        $deleteRemoteResource = R::exec(
            "DELETE FROM remote_resource 
                 WHERE resource_name=:resource and 
                 module_id IN (SELECT id FROM module WHERE module_name=:module)",
            array(":module" => $package, ":resource" => $resource)
        );
    }

    public function addResource($package,$package_id,$resource, $content){
        //insert a row with the right URI to the package/resource
        $remres = R::dispense("remote_resource");
        $remres->module_id = $package_id;
        $remres->resource_name = $resource;
        $remres->module_name = $put_vars["module_name"];
        $remres->base_url = $put_vars["url"];
        // make sure this url ends with a /
        if(substr(strrev($remres->base_url),0,1) != "/"){
            $remres->base_url .= "/";
        }
        R::store($remres);
    }

    public function updateResource($package,$resource,$content){
        //update a URI to a resource
    }

}

?>
