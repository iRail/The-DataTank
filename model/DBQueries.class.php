<?php
/**
 * This class contains all queries executed by the model
 *
 * @package The-Datatank/model
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jens Segers
 */

class DBQueries {

    /**
     * Retrieve a specific resource's documentation
     */
    static function getGenericResourceDoc($package,$resource) {
        return R::getRow(
	    	"SELECT generic_resource.documentation as doc, creation_timestamp as creation_timestamp,
                        last_update_timestamp as last_update_timestamp 
                 FROM package,generic_resource,resource 
                 WHERE package.package_name=:package and resource.resource_name =:resource
                       and package.id=resource.package_id and resource.id = generic_resource.resource_id",
	        array(':package' => $package, ':resource' => $resource)
	    );
    }
    
    /**
     * Retrieve a specific resource's print methods
     * @Deprecated We no longer allow specifications of certain print methods, all formats are allowed
     */
    static function getGenericResourcePrintMethods($package,$resource) {
        return R::getRow(
    	    "SELECT generic_resource.print_methods as print_methods 
             FROM package,generic_resource,resource 
             WHERE package.package_name=:package and resource.resource_name =:resource 
       	     and package.id=resource.package_id and resource.id = generic_resource.resource_id",
    	    array(':package' => $package, ':resource' => $resource)
    	);
    }
    
    /**
     * Get a specific generic resource's type
     */
    static function getGenericResourceType($package, $resource) {
        return R::getRow(
            "SELECT generic_resource.type as type 
             FROM package,generic_resource,resource
             WHERE package.package_name=:package and resource.resource_name=:resource
                   and resource_id = resource.id
                   and package.id= resource.package_id",
            array(':package' => $package, ':resource' => $resource)
        );
    }
    
    static function getGenericResourceId($package, $resource) {
        return R::getRow(
            "SELECT gen_res_db.id
             FROM package, generic_resource as gen_res, generic_resource_db as gen_res_db
             WHERE package.package_name=:package_name 
             	   and gen_res.package_id=package.id 
             	   and gen_res.resource_name=:resource_name
            	   and gen_res.id=gen_res_db.resource_id",
            array(":package_name" => $package, ":resource_name" => $resource)
        );
    }
    
    /**
     * Retrieve all resources names and their package name
     */
    static function getAllGenericResourceNames() {
        return R::getAll(
            "SELECT resource.resource_name as res_name, package.package_name
             FROM package,generic_resource,resource 
             WHERE resource.package_id=package.id and generic_resource.resource_id=resource.id"
	    );
    }

    /**
     * Retrieve all packages
     */
    static function getAllPackages(){
        $results =  R::getAll(
            "SELECT package_name, timestamp
             FROM package"
        ); 
        
        $packages = array();
        foreach($results as $result){
            $package = new stdClass();
            $package->package_name = $result["package_name"];
            $package->timestamp = (int)$result["timestamp"];
            array_push($packages,$package);
        }

        return $packages;
    }
    
    
    /**
     * Check if a specific package has a specific resource
     */
    static function hasGenericResource($package,$resource) {
        return R::getRow(
    	    "SELECT count(1) as present 
             FROM package,generic_resource,resource 
             WHERE package.package_name=:package and resource.resource_name=:resource
             and resource.package_id=package.id and generic_resource.resource_id=resource.id",
    	    array(':package' => $package, ':resource' => $resource)
    	);
    } 
    
    /**
     * Store a generic resource
     */
    static function storeGenericResource($resource_id, $type, $documentation) {
        $genres = R::dispense("generic_resource");
        $genres->resource_id = $resource_id;
        $genres->type = $type;
        $genres->documentation = $documentation;
        $genres->timestamp = time();
        return R::store($genres);
    }
    
    /**
     * Delete a specific generic resource
     */
    static function deleteGenericResource($package,$resource) {
        return R::exec(
            "DELETE FROM generic_resource
                      WHERE resource_id IN 
                        (SELECT resource.id 
                         FROM package,resource
                         WHERE package.package_name=:package and resource.id = generic_resource.resource_id
                         and resource.resource_name =:resource and resource.package_id = package.id)",
            array(":package" => $package, ":resource" => $resource)
        );
    }
    
    /**
     * Retrieve a specific remote resource
     */
    static function getRemoteResource($package, $resource) {
       return R::getRow(
            "SELECT rem_rec.base_url as url ,rem_rec.package_name as package,
                    resource.resource_name as resource
             FROM   package,remote_resource as rem_rec,resource
             WHERE  package.package_name=:package and resource.resource_name =:resource
                    and package.id = package_id and resource_id = resource.id",
            array(':package' => $package, ':resource' => $resource)
        );
    }
    
    /**
     * Get all remote resource names with their package name
     */
    static function getAllRemoteResourceNames() {
        return R::getAll(
             "SELECT resource.resource_name as res_name, package.package_name
              FROM package,remote_resource,resource 
              WHERE resource.package_id=package.id 
                    and remote_resource.resource_id=resource.id"
        );
    }
    
    /**
     * Store a remote resource
     */
    static function storeRemoteResource($resource_id, $package_name, $base_url) {
        $remres = R::dispense("remote_resource");
        $remres->resource_id = $resource_id;
        $remres->package_name = $package_name;
        $remres->base_url = $base_url;
        return R::store($remres);
    }
    
    /**
     * Deletes all remote resources from a specific package
     */
    static function deleteRemotePackage($package) {
        return R::exec(
            "DELETE FROM remote_resource
                    WHERE package_id IN 
                                    (SELECT package.id 
                                     FROM package,resource 
                                     WHERE package.package_name=:package 
                                     and resource_id = resource.id
                                     and package_id = package.id)",
            array(":package" => $package)
        );
    }
    
    /**
     * Deletes a specific remote resource
     */
    static function deleteRemoteResource($package, $resource) {
        return R::exec(
            "DELETE FROM remote_resource
                    WHERE resource_id IN (SELECT resource.id 
                                   FROM package,resource 
                                   WHERE package.package_name=:package and package_id = package.id
                                   and resource_id = resource.id and resource.resource_name =:resource
                                   )",
            array(":package" => $package, ":resource" => $resource)
        );
    }
    
    /**
     * Retrieve a specific package's is
     */
    static function getPackageId($package) {
        return R::getRow(
            "SELECT package.id as id 
             FROM package 
             WHERE package_name=:package_name",
            array(":package_name"=>$package)
        );
    }
    
    /**
     * Store a package
     */
    static function storePackage($package) {
        $newpackage = R::dispense("package");
        $newpackage->package_name = $package;
        $newpackage->timestamp = time();
        return R::store($newpackage);
    }
    
    /**
     * Delete all resources from a package
     */
    static function deletePackageResources($package) {
        return R::exec(
            "DELETE FROM resource 
                    WHERE package_id IN
                    (SELECT id FROM package WHERE package_name=:package)",
            array(":package" => $package)
        );
    }
    
    /**
     * Delete a specific package
     */
    static function deletePackage($package) {
        return R::exec(
            "DELETE FROM package WHERE package_name=:package",
            array(":package" => $package)
        );
    }
    
    /**
     * Retrieve a specific resource's id
     */
    static function getResourceId($package_id, $resource) {
        return R::getRow(
            "SELECT resource.id
             FROM resource, package
             WHERE :package_id = package.id and resource_name =:resource and package_id = package.id",
            array(":package_id" => $package_id, ":resource" => $resource)
        );
    }

    /**
     * Get the creation timestamp from a resource
     */
    static function getCreationTime($package,$resource){

        $timestamp = R::getCell(
            "SELECT resource.creation_timestamp as timestamp
             FROM package,resource
             WHERE package.id = resource.package_id and package_name=:package and resource_name=:resource",
            array(":package" => $package,":resource" => $resource)
        );
        if(!$timestamp){
            return 0;
        }
        return (int)$timestamp;
    }

    /**
     * Get the creation timestamp from a package
     */
    static function getPackageCreationTime($package){
        $timestamp = R::getCell(
            "SELECT timestamp
             FROM package
             WHERE package_name =:package",
            array(":package" => $package)
        );
        if(!$timestamp){
            return 0;
        }
        return (int)$timestamp;
    }
    

    /**
     * Get the modification timestamp from a resource
     */
    static function getModificationTime($package,$resource){

        $timestamp = R::getCell(
            "SELECT resource.last_update_timestamp as timestamp
             FROM package,resource
             WHERE package.id = resource.package_id and package_name=:package and resource_name=:resource",
            array(":package" => $package,":resource" => $resource)
        );
        if(!$timestamp){
            return 0;
        }
        return (int)$timestamp;
    }
    

    /**
     * Store a resource
     */
    static function storeResource($package_id, $resource_name, $type) {
        $newResource = R::dispense("resource");
        $newResource->package_id = $package_id;
        $newResource->resource_name = $resource_name;
        $newResource->creation_timestamp = time();
        $newResource->last_update_timestamp = time();
        $newResource->type = $type;
        return R::store($newResource);
    }
    

    /**
     * Delete a specific resource
     */
    static function deleteResource($package, $resource) {
        return R::exec(
            "DELETE FROM resource 
             WHERE resource.resource_name=:resource and package_id IN
                   (SELECT id FROM package WHERE package_name=:package)",
            array(":package" => $package, ":resource" => $resource)
        );
    }
    
    /**
     * Get all published columns of a generic resource
     */
    static function getPublishedColumns($generic_resource_id) {
        return R::getAll(
            "SELECT column_name, is_primary_key,column_name_alias
             FROM published_columns
             WHERE generic_resource_id=:id",
            array(":id" => $generic_resource_id)
        );
    }
   
    /**
     * Store a published column
     */
    static function storePublishedColumn($generic_resource_id, $column_name,$column_alias, $is_primary_key) {
        $db_columns = R::dispense("published_columns");
        $db_columns->generic_resource_id = $generic_resource_id;
        $db_columns->column_name = $column_name;
        $db_columns->is_primary_key = $is_primary_key;
        $db_columns->column_name_alias = $column_alias;
        return R::store($db_columns);
    }
    
    /**
     * Get a specific foreign key relation
     */
    static function getForeignRelations($package,$resourcename) {
        $urls = array();

        $results = R::getAll(
            "SELECT package.package_name as package_name, resource.resource_name as resource_name, 
                    main_object_column_name as main_key, foreign_object_column_name as foreign_key
             FROM   foreign_relation as for_rel,
                    package,
                    resource,
                    generic_resource
             WHERE  for_rel.foreign_object_id = generic_resource.id and resource_id = resource.id and
                    package_id = package.id and for_rel.main_object_id IN
                                 (
                                  SELECT generic_resource.id
                                  FROM   resource,package,generic_resource
                                  WHERE  package.id = resource. package_id 
                                         and package.package_name = :package 
                                         and resource.resource_name = :resource
                                         and resource_id = resource.id
                                 )",

            array(":package" => $package,":resource" => $resourcename)

        );
        
        foreach($results as $result){
            $urls[ $result["main_key"] ] = Config::$HOSTNAME."".$result["package_name"]."/".$result["resource_name"]
                ."/".$result["resource_name"]."/?filterBy=".$result["foreign_key"]."&filterValue=";
        }
        return $urls;
    }

    /**
     * Store a foreign key relation
     */
    static function storeForeignRelation($package,$resource,$content) {

        $original_id_query = R::getAll(
            "SELECT generic_resource.id 
             FROM  package, resource,generic_resource
             WHERE package.package_name=:package_name and resource.package_id and resource.resource_name = :resource_name
                   and resource_id = resource.id",
            array(":package_name" => $package, ":resource_name" => $resource)
        );

        $original_id = $original_id_query[0]["id"];
        
        /*
         * Get the FK relation
         */

        $fk_package = $content["foreign_package"];
        $fk_resource = $content["foreign_resource"];
        $original_column_name = $content["original_column_name"];
        $foreign_column_name = $content["foreign_column_name"];
        $fk_id_query = R::getAll(
             "SELECT generic_resource.id 
              FROM  package, resource,generic_resource
              WHERE package.package_name=:package_name and package.id = package_id and resource.resource_name = :resource_name
                    and resource_id = resource.id",
              array(":package_name" => $fk_package, ":resource_name" => $fk_resource)
        );
        $fk_id = $fk_id_query[0]["id"];

        /*
         * Add the foreign relation to the back-end
         */
        $foreign_relation = R::dispense("foreign_relation");
        $foreign_relation->main_object_id             = $original_id;
        $foreign_relation->foreign_object_id          = $fk_id;
        $foreign_relation->main_object_column_name    = $original_column_name;
        $foreign_relation->foreign_object_column_name = $foreign_column_name;
        return R::store($foreign_relation);
    }
    
    /**
     * Delete a specific foreign key relation
     */
    static function deleteForeignRelation($package, $resource) {
        return R::exec(
                        "DELETE FROM foreign_relation 
                                WHERE main_object_id IN 
                                (SELECT gen_res.id 
                                 FROM  resource, package, generic_resource as gen_res
                                 WHERE package.package_name=:package and package.id=package_id and resource.resource_name=:resource 
                                       and gen_res.resource_id = resource.id) 
                                 OR 
                                 foreign_object_id IN 
                                (SELECT gen_res.id 
                                 FROM resource, package, generic_resource as gen_res
                                 WHERE package_package_name=:package and package.id=package_id and resource.resource_name=:resource 
                                       and gen_res.resource_id = resource.id
                                 )",
                        array(":package" => $package, ":resource" => $resource)
            );
    }

    /**
     * Get a specific DB resource
     */
    static function getDBResource($package, $resource) {
        return R::getRow(
            "SELECT generic_resource.id as gen_res_id,resource.id,db_name,db_table
           			,host,port,db_type,db_user,db_password 
         	FROM package,generic_resource_db,generic_resource,resource 
         	WHERE package.package_name=:package and package.id=resource.package_id 
    			  and resource.resource_name=:resource 
               	  and resource.id = generic_resource.resource_id 
                  and generic_resource.id = generic_resource_db.gen_resource_id",
            array(':package' => $package, ':resource' => $resource)
        );
    }
    
    
    /**
     * Store a DB resource
     */
    static function storeDBResource($resource_id, $db_type, $db_name, $db_table, $host, $port, $db_user, $db_password) {
        $dbresource = R::dispense("generic_resource_db");
        $dbresource->gen_resource_id = $resource_id;
        $dbresource->db_type = $db_type;
        $dbresource->db_name = $db_name;
        $dbresource->db_table = $db_table;
        $dbresource->host = $host;
        $dbresource->port = $port; // is this a required parameter ? default port?
        $dbresource->db_user = $db_user;
        $dbresource->db_password = $db_password;
        return R::store($dbresource);
    }


    /**
     * Delete a specific DB resource
     */
    static function deleteDBResource($package, $resource) {
         return R::exec(
            "DELETE FROM generic_resource_db
                    WHERE gen_resource_id IN 
                         (SELECT generic_resource.id 
                          FROM generic_resource,package,resource
                          WHERE resource.resource_name=:resource
                                and package.package_name=:package
                                and generic_resource.resource_id = resource.id
                                and package.id=resource.package_id)",
            array(":package" => $package, ":resource" => $resource));
    }
    
    /**
     * Retrieve a specific CSV resource
     */
    static function getCSVResource($package, $resource) {
        return R::getRow(
            "SELECT generic_resource.id as gen_res_id,generic_resource_csv.uri as uri,
                    generic_resource_csv.has_header_row as has_header_row
             FROM package,resource, generic_resource, generic_resource_csv
             WHERE package.package_name=:package and resource.resource_name=:resource
                   and package.id=resource.package_id 
                   and resource.id = generic_resource.resource_id
                   and generic_resource.id=generic_resource_csv.gen_resource_id",
            array(':package' => $package, ':resource' => $resource)
        );
    }
   
    /**
     * Store a CSV resource
     */
    static function storeCSVResource($resource_id, $uri,$has_header_row) {
        $resource = R::dispense("generic_resource_csv");
        $resource->gen_resource_id = $resource_id;
        $resource->uri = $uri;
        $resource->has_header_row = $has_header_row;
        return R::store($resource);
    }
    
    /**
     * Delete a specific CSV resource
     */
    static function deleteCSVResource($package, $resource) {
        return R::exec(
            "DELETE FROM generic_resource_csv
                    WHERE gen_resource_id IN 
                          (SELECT generic_resource.id FROM generic_resource,package,resource 
                           WHERE resource.resource_name=:resource
                                 and package.package_name=:package
                                 and resource_id = resource.id
                                 and package.id=package_id)",
            array(":package" => $package, ":resource" => $resource)
        );
    }
	
    /**
     * Retrieve a specific XLS resource
     */
    static function getXLSResource($package, $resource) {
        return R::getRow(
            "SELECT generic_resource.id as gen_res_id,generic_resource_xls.url as url,generic_resource_xls.sheet as sheet
             FROM package,resource, generic_resource, generic_resource_xls
             WHERE package.package_name=:package and resource.resource_name=:resource
                   and package.id=resource.package_id 
                   and resource.id = generic_resource.resource_id
                   and generic_resource.id=generic_resource_xls.gen_resource_id",
            array(':package' => $package, ':resource' => $resource)
        );
    }
   
    /**
     * Store a XLS resource
     */
    static function storeXLSResource($resource_id, $url, $sheet) {
        $resource = R::dispense("generic_resource_xls");
        $resource->gen_resource_id = $resource_id;
        $resource->url = $url;
		$resource->sheet = $sheet;
        return R::store($resource);
    }
    
    /**
     * Delete a specific XLS resource
     */
    static function deleteXLSResource($package, $resource) {
        return R::exec(
            "DELETE FROM generic_resource_xls
                    WHERE gen_resource_id IN 
                          (SELECT generic_resource.id FROM generic_resource,package,resource 
                           WHERE resource.resource_name=:resource
                                 and package.package_name=:package
                                 and resource_id = resource.id
                                 and package.id=package_id)",
            array(":package" => $package, ":resource" => $resource)
        );
    }

    /**
     * Retrieve a specific HTML Table resource
     */
    static function getHTMLTableResource($package, $resource) {
        return R::getRow(
            "SELECT generic_resource.id as gen_res_id,generic_resource_htmltable.url as url, generic_resource_htmltable.xpath as xpath
             FROM package,resource, generic_resource, generic_resource_htmltable
             WHERE package.package_name=:package and resource.resource_name=:resource
                   and package.id=resource.package_id 
                   and resource.id = generic_resource.resource_id
                   and generic_resource.id=generic_resource_htmltable.gen_resource_id",
            array(':package' => $package, ':resource' => $resource)
        );
    }
   
    /**
     * Store a HTML Table resource
     */
    static function storeHTMLTableResource($resource_id, $url, $xpath) {
        $resource = R::dispense("generic_resource_htmltable");
        $resource->gen_resource_id = $resource_id;
        $resource->url = $url;
		$resource->xpath = $xpath;
        return R::store($resource);
    }
    
    /**
     * Delete a specific HTML Table resource
     */
    static function deleteHTMLTableResource($package, $resource) {
        return R::exec(
            "DELETE FROM generic_resource_htmltable
                    WHERE gen_resource_id IN 
                          (SELECT generic_resource.id FROM generic_resource,package,resource 
                           WHERE resource.resource_name=:resource
                                 and package.package_name=:package
                                 and resource_id = resource.id
                                 and package.id=package_id)",
            array(":package" => $package, ":resource" => $resource)
        );
    }	

    /**
     * Retrieve a specific OGD Wien JSON resource
     */
    static function getOGDWienJSONResource($package, $resource) {
        return R::getRow(
            "SELECT generic_resource.id as gen_res_id,generic_resource_ogdwienjson.url as url
             FROM package,resource, generic_resource, generic_resource_ogdwienjson
             WHERE package.package_name=:package and resource.resource_name=:resource
                   and package.id=resource.package_id 
                   and resource.id = generic_resource.resource_id
                   and generic_resource.id=generic_resource_ogdwienjson.gen_resource_id",
            array(':package' => $package, ':resource' => $resource)
        );
    }
   
    /**
     * Store a OGD Wien JSON resource
     */
    static function storeOGDWienJSONResource($resource_id, $url) {
        $resource = R::dispense("generic_resource_ogdwienjson");
        $resource->gen_resource_id = $resource_id;
        $resource->url = $url;
        return R::store($resource);
    }
    
    /**
     * Delete a specific OGD Wien JSON resource
     */
    static function deleteOGDWienJSONResource($package, $resource) {
        return R::exec(
            "DELETE FROM generic_resource_ogdwienjson
                    WHERE gen_resource_id IN 
                          (SELECT generic_resource.id FROM generic_resource,package,resource 
                           WHERE resource.resource_name=:resource
                                 and package.package_name=:package
                                 and resource_id = resource.id
                                 and package.id=package_id)",
            array(":package" => $package, ":resource" => $resource)
        );
    }

    /**
     * Delete published columns for a certain generic resource
     */
    static function deletePublishedColumns($package,$resource){
        return R::exec(
            "DELETE FROM published_columns
                    WHERE generic_resource_id IN
                    ( 
                      SELECT generic_resource.id 
                      FROM   generic_resource,resource,package
                      WHERE  package_name = :package 
                             and package_id = package.id
                             and resource_name = :resource 
                             and resource_id = resource.id
                    )",
            array(":package" => $package, ":resource" => $resource)
            
        );
    }
}
?>
