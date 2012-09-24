<?php
/**
 * Class to delete a remote resource
 *
 * @package The-Datatank/model/resources/delete
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */
include_once("ADeleter.class.php");

class RemoteResourceDeleter extends ADeleter{

    /**
     * execution method
     */
    public function delete(){

        /**
         * delete bottom up
         */
        DBQueries::deleteRemoteResource($this->package,$this->resource);
        DBQueries::deleteResource($this->package,$this->resource);
        DBQueries::deletePackage($this->package);
    }
}
?>
