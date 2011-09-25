<?php
/**
 * Class to delete a generic resource
 *
 * @package The-Datatank/model/resources/delete
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */
include_once("ADeleter.class.php");

class GenericResourceDeleter extends ADeleter{
    
    public function __construct($package,$resource){
        parent::__construct($package,$resource);
    }

    /**
     * execution method
     */
    public function delete(){
        $resource = new GenericResource($this->package,$this->resource);
        $strategy = $resource->getStrategy();
        $strategy->onDelete($package,$resource);

        DBQueries::deleteForeignRelation($package,$resource);
            
        // delete any published columns entry
        DBQueries::deletePublishedColumns($package,$resource);
        
        //now the only thing left to delete is the main row
        DBQueries::deleteGenericResource($package, $resource);

    }
}
?>