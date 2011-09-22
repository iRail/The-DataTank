<?php
/**
 * This class will handle all resources needed by the core. For instance the resources provided by the TDTInfo package.
 *
 * @package The-Datatank/model
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert
 */

class CoreResourceFactory extends AResourceFactory {

    public function __construct(){
        
    }

    public function createCreator($package,$resource, $parameters){
        
    }
    
    public function createReader($package,$resource, $parameters){
        
    }
    
    public function createUpdater($package,$resource, $parameters){
        
    }
    
    public function createDeleter($package,$resource){
        
    }
    
    public function makeDoc($doc){
        //ask every resource we have for documentation
        
    }
}

?>
