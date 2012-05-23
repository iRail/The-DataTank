<?php
/**
 * An class for XML data
 *
 * @package The-Datatank/model/resources/strategies
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */
include_once("model/resources/AResourceStrategy.class.php");
include_once("model/DBQueries.class.php");

class XML extends AResourceStrategy{
  
    public function read(&$configObject,$package,$resource){

        $xmlString = file_get_contents($configObject->uri);

        $xml = simplexml_load_string($xmlString);
        $json = json_encode($xml);
        $array = json_decode($json,TRUE);

        return $array;
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
           "uri" => "The uri to the xml document."
       );
       
   }
   
   public function documentReadParameters(){
       return array();
   }
   
   public function documentUpdateParameters(){
       return array();
   }


   // This will probably contain the upper level elements of the xml document, or won't be used at all
   public function getFields($package,$resource){
       return array();
   }
   
}
?>
