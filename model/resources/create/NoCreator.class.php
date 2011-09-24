<?php
/**
 * AClass for a request for creating a resource, while no addition can be done
 * i.e. Creation of an installed resource can not be done by an API call
 *
 * @package The-Datatank/model/resources/create
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */

/**
 * When creating a resource, we always expect a PUT method!
 */
class NoCreator extends ACreator{

    protected $parameters = array();
    protected $requiredParameters = array();
    
    public function setParameter($key,$value){
        
    }


    /**
     * execution method
     */
    public function create(){
        throw new ResourceTDTException("You cannot create this type of resource with an API call.");
    }

    /**
     * get all the parameters to create a resource
     */
    public function getCreateParameters(){
        return $this->parameters;
    }
    
    /**
     * get the required parameters
     */
    public function getCreateRequiredParameters(){
        return $this->requiredParameters;
    }
    
    
    /**
     * get the documentation about the addition of a resource
     */
    public function getCreateDocumentation(){
        return "This is a class that prohibits users to create a certain resource. i.e. installed resources cannot be created via an API-call.";
    }
    
}
?>