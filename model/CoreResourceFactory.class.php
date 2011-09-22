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

    private static $instance;
    
    private function __construct(){

    }
    
    public static function getInstance(){
        if(is_null(self::$instance)){
            self::$instance = new CoreResourceFactory();
        }
        return self::$instance;
    }
    


    public function createCreater($package,$resource){
        
    }
    
    public function createReader($package,$resource){
        
    }
    
    public function createUpdater($package,$resource){
        
    }
    
    public function createDeleter($package,$resource){
        
    }
    
    public function makeDoc($doc){
        
    }
}

?>
