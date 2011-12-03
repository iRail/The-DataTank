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
abstract class ATabularData extends AResourceStrategy{

    /*
     * This functions associates column names with a certain resource
     */
    protected function evaluateColumns($columns,$PK,$gen_res_id){
        foreach($columns as $column => $column_alias){
            /* // replace whitespaces in columns by underscores */
            $formatted_column = preg_replace('/\s+/','_',$column_alias); 
            DBQueries::storePublishedColumn($gen_res_id, $column,$column_alias,($PK != "" && $PK == $column?1:0));
        }
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

}
?>