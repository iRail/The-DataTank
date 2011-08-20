<?php
  /**
   * This class will handle a remote resource and connect to another DataTank instance for their data
   *
   * @package The-Datatank/factories
   * @copyright (C) 2011 by iRail vzw/asbl
   * @license AGPLv3
   * @author Pieter Colpaert
   */
include_once("resources/RemoteResource.class.php");

class RemoteResourceFactory extends AResourceFactory{
    
    /*
     * This object contains all the information from the last used
     * requested object. This way we wont have to call the remote resource
     * every single call to this factory. If we receive a call
     * for another resource, we replace it by the newly asked factory.
     * Note: Look into dirty read of object, state that the object has been
     * validated according to the request, but before the reading of the object
     * is done, another request is done .... First look of it, i'd say with a singleton
     * and I think(hope) that function calls are processed on a sequential base and not a
     * parallel base.
     */
    private $currentRemoteResource;
    
    private static $factory;

    private function __construct(){
        $this->currentRemoteResource = new stdClass();
        $this->currentRemoteResource->module = "";
        $this->currentRemoteResource->resource = "";
    }
    
    public static function getInstance(){
	if(!isset(self::$factory)){
	    self::$factory = new RemoteResourceFactory();
	}
	return self::$factory;
    }

    /**
     * @return returns a string containing the documentation about the resource. It returns an empty string when the resource could not be found
     */
    public function getResourceDoc($module, $resource){
        if( $this->currentRemoteResource->module != $module || $this->currentRemoteResource->resource != $resource){
            $this->fetchResource($module,$resource);
        }
        
        return $this->currentRemoteResource->data["doc"];
    }
    

    /**
     * @return returns an associative array with the documentation for each parameter for a specific resource 
     */
    public function getResourceParameters($module, $resource){
	//todo - get from remote
        if( $this->currentRemoteResource->module != $module || $this->currentRemoteResource->resource != $resource){
            $this->fetchResource($module,$resource);
        }
        return $this->currentRemoteResource->data["parameters"];
        
    }    

    /**
     * @return returns an array with all required parameters
     */
    public function getResourceRequiredParameters($module,$resource){
	//todo - get from remote
        if( $this->currentRemoteResource->module != $module || $this->currentRemoteResource->resource != $resource){
            $this->fetchResource($module,$resource);
        }
        return $this->currentRemoteResource->data["requiredparameters"];
    }

    public function getAllowedPrintMethods($module,$resource){
	//todo - get from remote
        if( $this->currentRemoteResource->module != $module || $this->currentRemoteResource->resource != $resource){
            $this->fetchResource($module,$resource);
        }
        return $this->currentRemoteResource->data["formats"];
    }
    

    public function hasResource($module,$resource){
	$rn = $this->getAllResourceNames();
	if(isset($rn[$module])){ 
	    return in_array($resource, $rn[$module]);
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
    public function getResource($module,$resource){
        if( $this->currentRemoteResource->module != $module || $this->currentRemoteResource->resource != $resource){
            $this->fetchResource($module,$resource);
        }
	return new RemoteResource($this->currentRemoteResource->remote_module, $resource,
                                  $this->currentRemoteResource->reqparams,
                                  $this->currentRemoteResource->base_url);
    }

    private function fetchResource($module,$resource){
        
        R::setup(Config::$DB,Config::$DB_USER,Config::$DB_PASSWORD);
	$param = array(':module' => $module, ':resource' => $resource);
	$result = R::getAll(
	    "select rem_rec.base_url as url ,rem_rec.module_name as module,rem_rec.resource_name as resource
             from module,remote_resource as rem_rec
             where module.module_name=:module and rem_rec.resource_name =:resource
             and module.id=rem_rec.module_id",
	    $param
	);
        if(sizeof($result) == 0){
            throw new MethodOrModuleNotFoundTDTException("Cannot find the remote resource with module and resource pair as: ".$module."/".$resource);
        }else{
            $url = $result[0]["url"]."TDTInfo/Modules/".$result[0]["module"]."/".$result[0]["resource"]."/?format=php";
        }
        
        $request = TDT::HttpRequest($url);
        $data = unserialize($request->data);
        $this->currentRemoteResource = new stdClass();
        $this->currentRemoteResource->module = $module;
        $this->currentRemoteResource->remote_module = $result[0]["module"];
        $this->currentRemoteResource->resource = $resource;
        $this->currentRemoteResource->data = $data;
        $this->currentRemoteResource->base_url = $result[0]["url"];
        $this->currentRemoteResource->parameter_keys = array_keys($data["parameters"]);
        $this->currentRemoteResource->reqparams = $data["requiredparameters"];
        
    }
    
    
  }

?>
