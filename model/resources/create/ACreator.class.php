<?php
/**
 * AClass for creating a resource
 *
 * @package The-Datatank/model/resources/create
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */
abstract class ACreator{

    protected $parameters = array();
    protected $requiredParameters = array();

    public function __construct(){
        $this->parameters["resource_type"] = "The type of the resource.";
        $this->parameters["package"] = "Name of the package to add the resource to.";
        $this->parameters["resource"] = "Name of the resource.";

        $this->requiredParameters[] = "resource_type";
        $this->requiredParameters[] = "package";
        $this->requiredParameters[] = "resource";
    }    

    /**
     * process the parameters
     */
    public function processCreateParameters($parameters){
	foreach($parameters as $key => $value){
            //check whether this parameter is in the documented parameters
            if(!isset($this->parameters[$key])){
                throw new ParameterDoesntExistTDTException($key);
            }else if(in_array($key,$this->requiredParameters)){
                    $this->$key = $value;
            }
        }
        /*
         * check if all requiredparameters have been set
         */
        foreach($this->requiredParameters as $key){
            if($this->$key == ""){
                throw new ParameterTDTException("Required parameter ".$key ." has not been passed");
            }
        }

        /*
         * set the parameters
         */
        foreach($parameters as $key => $value){
            $this->setParameter($key,$value);
        }
    }

    /**
     * set parameters, we leave this to the subclass
     */
    abstract protected function setParameter($key,$value);

    /**
     * execution method
     */
    abstract public function create();

    /**
     * get all the parameters to create a resource
     * @return hash with key = parameter name and value = documentation about the parameter
     */
    public function getCreateParameters(){
        return $this->parameters;
    }
    
    /**
     * get the required parameters
     * @return array with all of the required parameters
     */
    public function getCreateRequiredParameters(){
        return $this->requiredParameters;
    }

    /**
     * get the documentation about the addition of a resource
     * @return string containing a description about the class
     */
    abstract public function getCreateDocumentation();

    /**
     * make package id
     * @return id of the package
     */
    protected function makePackage(){
        // TODO put this in DBQueries
        $result = R::getAll(
            "SELECT package.id as id 
             FROM package 
             WHERE package_name=:package_name",
            array(":package_name"=>$this->package)
        );
        
        if(sizeof($result) == 0){
            $newpackage = R::dispense("package");
            $newpackage->package_name = $package;
            $newpackage->timestamp = time();
            $id = R::store($newpackage);
            return $id;
        }
        return $result[0]["id"];
    }
    
    /**
     * make resource id
     * @return id of the resource
     */
    protected function makeResource($package_id){
        
        // TODO put this in DBQueries.
        $checkExistence = R::getAll(
            "SELECT resource.id
             FROM resource, package
             WHERE :package_id = package.id and resource.resource_name =:resource and resource.package_id = package.id",
            array(":package_id" => $package_id, ":resource" => $this->resource)
        );

        if(sizeof($checkExistence) == 0){
            $newResource = R::dispense("resource");
            $newResource->package_id = $package_id;
            $newResource->resource_name =  $this->resource;
            $newResource->creation_timestamp = time();
            $newResource->last_update_timestamp = time();
            $newResource->type =  $this->resource_type;
            return R::store($newResource);
        }
        return $checkExistence[0]["id"];
    }
}
?>