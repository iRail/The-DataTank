<?php
/**
 * This will add ontological information to a 
 * @package The-Datatank/model/resources/actions
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert
 */

include_once("AUpdater.class.php");

class RdfMapping extends AUpdater{
    

    public function __construct($package,$resource){
        parent::__construct($package,$resource);
        $this->parameters["rdf_mapping_method"] = "The method by which the rdf should be mapped.";
        $this->parameters["rdf_mapping_bash"] = "If this is set, that indicates that there are multiple related resources to be mapped.";
        $this->parameters["rdf_mapping_class"] = "The RDF class to map to the resource.";
        $this->parameters["rdf_mapping_nmsp"] = "The namespace of the RDF mapping.";
        
        $this->requiredParameters = array_keys($this->parameters);
    }
    
    public function update(){
        $rdfmapper = new RDFMapper();
        //need full path for adding semantics!!
        $resource = RequestURI::getInstance()->getRealWorldObjectURI();
        $params = array();
        foreach(array_keys($parameters) as $key){
            $params[] = $this->$key;
        }
        
        $rdfmapper->update($this->package,$this->resource,$params);
    }
}
?>