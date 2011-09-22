<?php
/**
 * AClass for creating a resource
 *
 * @package The-Datatank/model/resources/create
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */

include_once("model/ICreator.php");

/**
 * When creating a resource, we always expect a PUT method!
 */
abstract class ACreator implements ICreator{

    protected $parameters = array();
    protected $requiredParameters = array();
    protected $optionalParameters = array();

    public function __ACreator(){
        $this->parameters["resource_type"] = "The type of the resource.";
        $this->parameters["package"] = "Name of the package to add the resource to.";
        $this->parameters["resource"] = "Name of the resource.";

        $this->requiredParameters["resource_type"] = "";
        $this->requiredParameters["package"] = "";
        $this->requiredParameters["resource"] = "";
    }    

    /**
     * process the parameters
     */
    public function processCreateParameters($parameters){
        // process every parameters passed along with the creation requests
        // and assign them to the correct parameter belonging to the Creator-class
        $allowedParameters = array_keys($this->parameters);
	foreach($allowedParameters as $key => $value){
            //check whether this parameter is in the documented parameters
            if(isset($this->requiredParameters[$key])){
                $this->optionalParameters[$key] = $value;
            }else if(isset($this->optionalParameters[$key])){
                $this->requiredParameters[$key] = $value;
            }else{
                throw new ParameterDoesntExistTDTException($key);
            }
        }
        // check if all requiredparameters have been set
       
        foreach($this->requiredParameters as $key => $value){
            if($value == ""){
                throw new ParameterTDTException("Required parameter ".$key ." has not been passed");
            }
        }
    }

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
     * get the optional parameters
     */
    public function getOptionalParameters(){
        return $this->optionalParameters;
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
            array(":package_name"=>$this->requiredParameters["package"])
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
            array(":package_id" => $package_id, ":resource" => $this->requiredParameters["resource"])
        );

        if(sizeof($checkExistence) == 0){
            $newResource = R::dispense("resource");
            $newResource->package_id = $package_id;
            $newResource->resource_name =  $this->requiredParameters["resource"];
            $newResource->creation_timestamp = time();
            $newResource->last_update_timestamp = time();
            $newResource->type =  $this->requiredParameters["resource_type"];
            return R::store($newResource);
        }
        return $checkExistence[0]["id"];
    }


}
?>