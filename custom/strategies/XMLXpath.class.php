<?php

/**
 * This class handles an XML file
 *
 * @package The-Datatank/custom/strategies
 * @copyright (C) 2011 by Digipolis
 * @license AGPLv3
 * @author Dries Droesbeke
 */
include_once ("custom/strategies/ATabularData.class.php" );
include_once ("aspects/logging/BacklogLogger.class.php" );
class XMLXpath extends ATabularData {
       
        /**
        * The parameters returned are required to make this strategy work.
        *
        * @return array with parameter => documentation pairs
        */
        public function documentCreateRequiredParameters() {
               return array (
                            "uri",
                            "xpath"
               );
       }
       
        /**
        * The parameters ( array keys ) returned all of the parameters that can be
        * used to create a strategy.
        *
        * @return array with parameter => documentation pairs
        */
        public function documentCreateParameters() {
              $this-> parameters ["uri" ] = "The uri to the CSV file" ;
              $this-> parameters ["xpath" ] = "The xpath to the startnode." ;
               return $this->parameters ;
       }
       
        /**
        * Returns an array with parameter => documentation pairs that can be used
        * to read a CSV resource.
        *
        * @return array with parameter => documentation pairs
        */
        public function documentReadParameters() {
               return array ();
       }
       
        /**
        * Read a resource
        *
        * @param $configObject The
        *            configuration object containing all of the parameters
        *            necessary to read the resource.
        * @param $package The
        *            package name of the resource
        * @param $resource The
        *            resource name of the resource
        * @return $mixed An object created with fields of a XML file.
        */
        public function read(&$configObject, $package, $resource) {
               parent::read ( $configObject, $package, $resource );
              
              $arrayOfRowObjects = array ();
              
               try {
                     
                      // get data
                     $arrayOfRowObjects = $this->parseXmlUri ( array (
                                   "uri" => $configObject->uri ,
                                   "xpath" => $configObject->xpath ,
                                   "columns" => $configObject->columns
                     ) );
                     
                      return $arrayOfRowObjects;
              } catch ( Exception $ex ) {
                      throw new CouldNotGetDataTDTException ( $configObject->uri );
              }
       }
        protected function isValid($package_id, $generic_resource_id) {
              $arrayOfRowObjects = $this->parseXmlUri ( array (
                            "uri" => $this->uri ,
                            "xpath" => $this->xpath ,
                            "columns" => (isset ( $this->columns ) ? $this->columns : null )
              ) );
              
               if (count ( $arrayOfRowObjects ) == 0) {
                      return false;
              }
              $arr_columns = get_object_vars ( $arrayOfRowObjects [0] );
              
               if (! isset ( $this->columns )) {
                     $this-> columns = array ();
                      foreach ( $arr_columns as $key => $value ) {
                           $this-> columns [] = $key;
                     }
              }
              
               if (! isset ( $this->column_aliases )) {
                     $this-> column_aliases = array ();
              }
              
               if (! isset ( $this->PK )) {
                     $this-> PK = "";
              }
               return true;
       }
        private function formatKey($key) {
              $key = strtolower ( $key );
               return $key;
       }
        private function parseXmlUri($arr_config) {
              $arrayOfRowObjects = array();
              
              $ch = curl_init ();
              curl_setopt ( $ch, CURLOPT_URL, $arr_config [ 'uri'] );
              curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
              $xml_data = curl_exec ( $ch );
              curl_close ( $ch );
              
              $obj_xml = new SimpleXMLElement ( $xml_data );
              
       
              
              
              
              
              $arr_nodes = $obj_xml->xpath ( $arr_config [ 'xpath'] );
              
               foreach ( $arr_nodes as $obj_node ) {
                     $resultobject = new stdClass ();
                     
                     $resultobject = $this->_node2xml($resultobject , $obj_node,"" );
                     
                    
                     $arrayOfRowObjects [] = $resultobject;
              }
               return $arrayOfRowObjects;
       }


        private function _node2xml($obj_fields , $obj_xml,$parent_prefix=""){
       
              
               foreach ( $obj_xml->children() as $key => $obj_child ) {
                      if($parent_prefix <> "" ){
                           $name = $parent_prefix . "_" . $key;
                     } else{
                           $name =  $key;
                     }
                     $name= $this->formatKey($name);
                     
                      //subitems
                      if(count($obj_child->children())>0){
                           $obj_fields = $this->_node2xml($obj_fields,$obj_child,$name);
                     } else{
                           $obj_fields->$name = (string)$obj_child;
                     }
                     
                      //attributes
                      foreach($obj_child->attributes() as $attr_key => $attr_value){
                           $name_attr = $name . "_attr_".(string)$attr_key;
                           $name_attr= $this->formatKey($name_attr);
                           $obj_fields->$name_attr = (string)$attr_value;
                     }

              }
              
              
              //namespaces
               foreach ( $obj_xml->getNameSpaces ( true ) as $ns_key => $ns_value ) {
                      foreach ( $obj_xml->children($ns_value) as $key => $obj_child ) {
                            if($parent_prefix <> "" ){
                                  $name = $parent_prefix . "_" . $ns_key. "_". $key;
                           } else{
                                  $name =  $ns_key. "_". $key;
                           }
          
                           $name= $this->formatKey($name);
                                  
                            //subitems
                            if(count($obj_child->children())>0){
                                  $obj_fields = $this->_node2xml($obj_fields,$obj_child,$name);
                           } else{
                                  $obj_fields->$name = (string)$obj_child;
                           }
                                  
                            //attributes
                            foreach($obj_child->attributes() as $attr_key => $attr_value){
                                  $name_attr = $name . "_attr_".(string)$attr_key;
                                  $name_attr= $this->formatKey($name_attr);
                                  $obj_fields->$name_attr = (string)$attr_value;
                           }
                     
                     }
              }
              
              


              
              
               return $obj_fields;
              
       }
       
       
       
       
}


?>
