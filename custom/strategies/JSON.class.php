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
    
    public function read(&$configObject){ 
        return json_decode(file_get_contents($configObject->url));
    }

    public function isValid($package_id,$generic_resource_id){
        $result = json_decode(file_get_contents($this->url));
        if($result != true){
            throw new ResourceAdditionTDTException("Could not transfrom the json data from ". $this->url ." to a php object model, please check if the json is valid.");
        }
    }
    

    public function onUpdate($package, $resource){
        
    }

    public function documentCreateRequiredParameters(){
        return array("url");
    }
    
    public function documentReadRequiredParameters(){
        return array();
    }
    

    public function documentUpdateRequiredParameters(){
        return array();
    }
    

   public function documentCreateParameters(){
       return array(
           "url" => "The url to the json document."
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