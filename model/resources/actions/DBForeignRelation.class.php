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
        $original_id_query = R::getAll(
            "select gen_res_db.id from 
             package, generic_resource as gen_res, generic_resource_db as gen_res_db
             where package.package_name=:package_name and gen_res.package_id=package.id and gen_res.resource_name=:resource_name
            and gen_res.id=gen_res_db.resource_id",
            array(":package_name" => $package, ":resource_name" => $resource)
        );

        $original_id = $original_id_query[0]["id"];
        
        /*
         * Get the FK relation
         */
        $fk_package = $content["foreign_package"];
        $fk_resource = $content["foreign_resource"];
        $original_column_name = $content["original_column_name"];
        $fk_id_query = R::getAll("select gen_res_db.id from 
             package, generic_resource as gen_res, generic_resource_db as gen_res_db
             where package.package_name=:package_name and gen_res.package_id=package.id and gen_res.resource_name=:resource_name
             and gen_res.id=gen_res_db.resource_id",
                                 array(":package_name" => $fk_package, ":resource_name" => $fk_resource)
        );
        $fk_id = $fk_id_query[0]["id"];

        /*
         * Add the foreign relation to the back-end
         */
        $db_foreign_relation = R::dispense("db_foreign_relation");
        $db_foreign_relation->main_object_id = $original_id;
        $db_foreign_relation->foreign_object_id = $fk_id;
        $db_foreign_relation->main_object_column_name = $original_column_name;
        return R::store($db_foreign_relation);
    }
}
?>