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
         R::setup(Config::$DB,Config::$DB_USER,Config::$DB_PASSWORD);
         
	$resultset = R::getAll(
             "SELECT resource.resource_name as res_name, package.package_name
              FROM package,remote_resource,resource 
              WHERE resource.package_id=package.id 
                    and remote_resource.resource_id=resource.id"
	);
        
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
        
        R::setup(Config::$DB,Config::$DB_USER,Config::$DB_PASSWORD);
	$param = array(':package' => $package, ':resource' => $resource);
	$result = R::getAll(
	    "SELECT rem_rec.base_url as url ,rem_rec.package_name as package,
                    resource_name as resource
             FROM  package,remote_resource as rem_rec,resource
             WHERE package_name=:package and resource_name =:resource
                   and package.id = package_id and resource_id = resource.id",
	    $param
	);
        if(sizeof($result) == 0){
            throw new ResourceOrPackageNotFoundTDTException("Cannot find the remote resource with package and resource pair as: ".$package."/".$resource);
        }else{
            $url = $result[0]["url"]."TDTInfo/Resources/".$result[0]["package"]."/".$result[0]["resource"]."/?format=php";
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
                    WHERE package_id IN 
                                    (SELECT package.id 
                                     FROM package,resource 
                                     WHERE package_name=:package 
                                     and resource_id = resource.id
                                     and package_id = package.id)",
            array(":package" => $package)
        );
    }

    public function deleteResource($package, $resource){
        $deleteRemoteResource = R::exec(
            "DELETE FROM remote_resource
                    WHERE package_id IN (SELECT package.id 
                                   FROM package,resource 
                                   WHERE package_name=:package and package_id = package.id
                                   and resource_id = resource.id and resource_name =:resource
                                   )",
            array(":package" => $package, ":resource" => $resource)
        );
    }

    public function addResource($package,$resource, $content){
        //insert a row with the right URI to the package/resource
        $model = ResourcesModel::getInstance();
        $resource_id = $model->getResourceId($package_id,$resource);

        $remres = R::dispense("remote_resource");
        $remres->resource_id = $resource_id;
        $remres->package_name = $content["package_name"];
        $remres->base_url = $content["url"];
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
