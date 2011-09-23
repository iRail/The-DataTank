<?php
/**
 * Class to delete a remote resource
 *
 * @package The-Datatank/model/resources/delete
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */
include_once("ADelete.class.php");

class RemoteResourceDelete extends ADelete{
    
    public function __construct($package,$resource){
        parent::__construct($package,$resource);
    }

    /**
     * execution method
     */
    public function delete(){
       DBQueries::deleteRemotePackage($package);
    }
}
?>