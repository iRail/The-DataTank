<?php
/**
 * An abstract class for XML data
 *
 * @package The-Datatank/model/resources/strategies
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */
include_once("model/resources/AResourceStrategy.class.php");
include_once("model/DBQueries.class.php");
include_once("includes/xmlLib.php");

class XML extends AResourceStrategy{
  
    public function read(&$configObject){
        // streams
        $xmlReader = new XMLReader;
        $xmlReader->open($configObject->url);
        return $this->xml2assoc($xmlReader);
    } 

    private function xml2assoc($xml) { 
        $tree = null; 
        while($xml->read()) 
            switch ($xml->nodeType) { 
                case XMLReader::END_ELEMENT: return $tree; 
                case XMLReader::ELEMENT: 
                    $node = array('tag' => $xml->name, 'value' => $xml->isEmptyElement ? '' : $this->xml2assoc($xml)); 
                    if($xml->hasAttributes) 
                        while($xml->moveToNextAttribute()) 
                            $node['attributes'][$xml->name] = $xml->value; 
                    $tree[] = $node; 
                    break; 
                case XMLReader::TEXT: 
                case XMLReader::CDATA: 
                    $tree .= $xml->value; 
            } 
        return $tree; 
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
           "url" => "The url to the xml document."
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