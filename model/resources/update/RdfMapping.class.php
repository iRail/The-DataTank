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
        
    }
    
    public function update(){
        $rdfmapper = new RDFMapper();
        //need full path for adding semantics!!
        $resource = RequestURI::getInstance()->getRealWorldObjectURI();
        $rdfmapper->update($this->package,$this->resource,$content);
    }
}
?>