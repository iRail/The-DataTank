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
            DBQueries::storePublishedColumn($gen_res_id, $column, ($PK == $column?1:0));
        }
    }   
}
?>