<?php
/**
 * This action will add a 1:1 representation of 
 * 2 datatables which are added as database resources and have a FK relationship on a database level.
 *
 * @package The-Datatank/model/resources/actions
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */

include_once("model/resources/actions/AUpdateAction.class.php");

class DBForeignRelation extends AUpdateAction{
    

    public function __construct(){
        
    }
    
    public function update($package,$resource,$content){
        $result = DBQueries::getGenericResourceId($package, $resource);
        $original_id = $result["id"];
        
        /*
         * Get the FK relation
         */
        $fk_package = $content["foreign_package"];
        $fk_resource = $content["foreign_resource"];
        $original_column_name = $content["original_column_name"];
        $fk_id_query = DBQueries::getGenericResourceId($fk_package, $fk_resource);
        $fk_id = $fk_id_query["id"];

        /*
         * Add the foreign relation to the back-end
         */
        return DBQueries::storeForeignRelation($original_id, $fk_id, $original_column_name);
    }
}
?>