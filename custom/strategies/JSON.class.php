<?php
/**
 * An abstract class for JSON data
 *
 * @package The-Datatank/model/resources/strategies
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */
include_once("model/resources/AResourceStrategy.class.php");
include_once("model/DBQueries.class.php");

class JSON extends AResourceStrategy{
    
    public function read(&$configObject,$package,$resource){ 
        return json_decode(file_get_contents($configObject->uri));
    }

    public function isValid($package_id,$generic_resource_id){
        $result = json_decode(file_get_contents($this->uri));
        if($result != true){
            throw new ResourceAdditionTDTException("Could not transfrom the json data from ". $this->uri ." to a php object model, please check if the json is valid.");
        }
    }
    

    public function onUpdate($package, $resource){
        
    }

    public function documentCreateRequiredParameters(){
        return array("uri");
    }
    
    public function documentReadRequiredParameters(){
        return array();
    }
    

    public function documentUpdateRequiredParameters(){
        return array();
    }
    

   public function documentCreateParameters(){
       return array(
           "uri" => "The uri to the json document."
       );  
   }
   
   public function documentReadParameters(){
       return array();
   }
   
   public function documentUpdateParameters(){
       return array();
   }

   public function getFields($package,$resource){
       return array();
   }

}
?>
