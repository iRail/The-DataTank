<?php

/**
 * This will get a resource description from the databank and add the right strategy to process the call to the GenericResource class
 *
 * @package The-Datatank/model
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert
 * @author Jan Vansteenlandt
 */
include_once("model/resources/GenericResource.class.php");

class GenericResourceFactory extends AResourceFactory {

    public function createCreator($package,$resource, $parameters){
        include_once("model/resources/create/GenericResourceCreator.class.php");
        if(!isset($parameters["generic_type"])){
            throw new ResourceAdditionTDTException("generic type hasn't been set");
        }
        $creator = new GenericResourceCreator($parameters["generic_type"]);
        //TODO
    }
    
    public function createReader($package,$resource, $parameters){
        include_once("model/resources/create/GenericResourceReader.class.php");
        //Todo: processParameters
        foreach($parameters as $key => $value){
            
        }

        return new GenericResourceReader($parameters["generic_type"]); 
    }
        
    public function createDeleter($package,$resource){
        
    }
    
    public function makeDoc($doc){

    }

}

?>
