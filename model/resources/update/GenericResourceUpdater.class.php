<?php
/**
 * This will proxy the updater to a generic strategy resource
 * 
 * @package The-Datatank/model/resources/update
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert
 */
include_once("AUpdater.class.php");
include_once("model/DBQueries.class.php");

class GenericResourceUpdater extends AUpdater {

    private $delimiter = ";";

    public function __construct($package, $resource, $RESTparameters) {
        parent::__construct($package, $resource, $RESTparameters);
    }

    public function getParameters(){
        return array(
            "tags" => "A list of tags, separated by a semi-colon."
        );
    }

    public function getRequiredParameters() {
        return array(
             "tags"
        );
    }

    protected function setParameter($key, $value) {
        $this->$key = $value;
    }

    public function update() {
        // get the id from the resource table
        $resourceId;

        if(DBQueries::hasGenericResource($this->package,$this->resource)){
            $packageId = DBQueries::getPackageId($this->package);
            $packageId = $packageId["id"];

            $resourceId = DBQueries::getResourceId($packageId,$this->resource);
            $resourceId = $resourceId["id"];

        }else{
            throw new ResourceUpdateTDTException("The specified resource isn't a generic resource.");
        }

        // store the tags in the tag table
        // get all the id's from the tags in the tag table

        $tagArray = explode($this->delimiter,$this->tags);
        $tagIdArray = array();
        foreach($tagArray as $tag){
            $check = DBQueries::hasTag($tag);
            if($check == 0){
                $result = DBQueries::storeTag($tag);
                array_push($tagIdArray,$result);
            }else{
                array_push($tagIdArray,$check);
            }
        }

        // create entries in the coupling table resource-id -> tag-id
        foreach($tagIdArray as $tagId){
            // check if couple already exists, if not add it
            $result = DBQueries::hasResourceTag($resourceId,$tagId);
            if($result["present"] == 0){
                DBQueries::storeResourceTag($resourceId,$tagId);   
            }
        }
    }

    public function getDocumentation() {
        return "Perform an update on a resource, currently only Tags are supported.";
    }

}
?>