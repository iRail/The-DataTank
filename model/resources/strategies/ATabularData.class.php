<?php
/**
 * An abstract class for tabular data
 *
 * @package The-Datatank/model/resources/strategies
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert
 */

abstract class ATabularData extends AResourceStrategy{
    
    /*
     * This functions associates column names with a certain resource
     */
    protected function evaluateColumns($columns_concat,$PK,$gen_res_id){
        $columns = explode(";",$columns_concat);
        foreach($columns as $column){
            $db_columns = R::dispense("published_columns");
            $db_columns->generic_resource_id = $gen_res_id;
            $db_columns->column_name = $column;
            if($PK == $column){
                $db_columns->is_primary_key = 1;
            }else{
                $db_columns->is_primary_key = 0;
            }
            R::store($db_columns);
        }
    }   
}
?>