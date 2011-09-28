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

    public function __construct($package, $resource){
        $this->package = $package;
        $this->resource = $resource;
        
        $this->parameters["resource_type"] = "The type of the resource.";

        $this->requiredParameters[] = "resource_type";
    }

    /**
     * process the parameters
     */
    public function processParameters($parameters) {
        foreach ($parameters as $key => $value) {
            $this->setParameter($key, $value);
        }
        // check if all requiredparameters have been set
        foreach ($this->getRequiredParameters() as $key) {
            if (!in_array($key, array_keys($parameters))){
                throw new ParameterTDTException("Required parameter " . $key . " has not been passed");
            }
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
    public function getParameters(){
        return $this->parameters;
    }
    
    /**
     * get the required parameters
     * @return array with all of the required parameters
     */
    public function getRequiredParameters(){
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
    protected function makePackage($package){
        // TODO put this in DBQueries
        $result = R::getAll(
            "SELECT package.id as id 
             FROM package 
             WHERE package_name=:package_name",
            array(":package_name"=>$package)
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
    protected function makeResource($package_id, $resource, $resource_type){
        
        // TODO put this in DBQueries.
        $checkExistence = R::getAll(
            "SELECT resource.id
             FROM resource, package
             WHERE :package_id = package.id and resource.resource_name =:resource and resource.package_id = package.id",
            array(":package_id" => $package_id, ":resource" => $resource)
        );

        if(sizeof($checkExistence) == 0){
            $newResource = R::dispense("resource");
            $newResource->package_id = $package_id;
            $newResource->resource_name =  $resource;
            $newResource->creation_timestamp = time();
            $newResource->last_update_timestamp = time();
            $newResource->type =  $resource_type;
            return R::store($newResource);
        }
        return $checkExistence[0]["id"];
    }
}
?>