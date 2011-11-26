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
        //do nothing by default
    }

    public function documentUpdateParameters(){
        return array();
    }

    public function documentUpdateRequiredParameters(){
        return array();
    }

}
?>