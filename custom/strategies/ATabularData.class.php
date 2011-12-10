<?php
/**
 * An abstract class for tabular data
 *
 * @package The-Datatank/model/resources/strategies
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert
 */
include_once("model/resources/AResourceStrategy.class.php");
include_once("model/DBQueries.class.php");

abstract class ATabularData extends AResourceStrategy{

    protected $parameters = array();
    
    function __construct(){
        $this->parameters["columns"] = "An array that contains the name of the columns that are to be published, if empty array is passed every column will be published. Note that this parameter is not required, however if you do not have a header row, we do expect the columns to be passed along, otherwise there's no telling what the names of the columns are. This array should be build as column_name => column_alias or index => column_alias.";
    }

    public function onUpdate($package, $resource){
        
    }

    /**
     * Return an array with key = parameter and value = documentation about the parameter
     * @return hash array with param = documentation pairs for update purposes
     */
    public function documentUpdateParameters(){
        return array();
    }

    /**
     * Returns an array similar as the documentUpdateParameters, but now with the obligatory parameters
     * @return hash array with param = documentation pairs for update purposes, of which the parameters are obligatory.
     */
    public function documentUpdateRequiredParameters(){
        return array();
    }

   /**
     *  This function gets the fields in a resource
     * @param string $package
     * @param string $resource
     * @return array Array with column names mapped onto their aliases
     */
    public function getFields($package, $resource) {
        /*
         * First retrieve the values for the generic fields of the CSV logic
         * This is the uri to the file, and a parameter which states if the CSV file
         * has a header row or not.
         */
        $result = DBQueries::getGenericResourceId($package, $resource);
        $gen_res_id = $result["gen_resource_id"];

        $columns = array();

        // get the columns from the columns table
        $allowed_columns = DBQueries::getPublishedColumns($gen_res_id);

        /**
         * columns can have an alias, if not their alias is their own name
         */
        foreach ($allowed_columns as $result) {
            if ($result["column_name_alias"] != "") {
                $columns[(string) $result["column_name"]] = $result["column_name_alias"];
            } else {
                $columns[(string) $result["column_name"]] = $result["column_name"];
            }
        }
        
        return array_values($columns);
    }


}
?>