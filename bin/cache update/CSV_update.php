<?php
/**
 * This script is meant to be run by a cronjob so that the l2 cached resources (large csv files for example)
 * are being kept up to date on a regular basis.
 *
 * @package The-Datatank/bin
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */

include_once("model/DBQueries.class.php");
include_once("Config.class.php"); 
include_once('includes/rb.php'); 

/* /\** */
/*  * retrieve all of the CSV paged resources */
/*  *\/ */
 R::setup(Config::$DB,Config::$DB_USER,Config::$DB_PASSWORD); 
$paged_resources = DBQueries::getAllPagedCSVResources();

// for every paged_resource we're going to add them again, from top on, so we need the published columns information as well
for($i=0; $i < count($paged_resources); $i++){
    $gen_id = $paged_resources[$i]["gen_res_id"];
    $columns = DBQueries::getPublishedColumns($gen_id);
    $paged_resources[$i]["columns"] = $columns;
}

/**
 * delete the l2 cache entries of them
 * and refresh them with a fresh caching of the files.
 * NOTE: we're going to delete the entire entry of it !!
 * because a csv could be changed from being paged to non paged
 * so an entire new put is necessary.
 */

foreach($paged_resources as $paged_resource){
    $package = $paged_resource["package_name"];
    $resource = $paged_resource["resource_name"];
    // http request for the deletion of the resource
    deleteResource($package,$resource);

    /**
     * Now create them again !
     */
    $uri = $paged_resource["uri"];
    $documentation = $paged_resource["documentation"];
    $has_header_row = $paged_resource["has_header_row"];
    $start_row = $paged_resource["start_row"];
    $delimiter = $paged_resource["delimiter"];
    $columns = array();
    $PK = "";
    // parse the columns database result
    foreach($paged_resource["columns"] as $columnresult){
        $columns[$columnresult["column_name"]] = $columnresult["column_name_alias"];
        if($columnresult["is_primary_key"] == 1){
            $PK = $columns[$columnresult["column_name"]];
        }
    }
    createResource($package,$resource,$columns,$PK,$documentation,$uri,$has_header_row,$delimiter,$start_row);
}

// Resource in this context is a CSV resource
function deleteResource($package,$resource){

    // HTTP authentication 
    $url = Config::$HOSTNAME . Config::$SUBDIR . $package . "/" . $resource;
    $ch = curl_init();     
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
    curl_setopt($ch, CURLOPT_URL, $url);  
    curl_setopt($ch, CURLOPT_USERPWD, Config::$API_USER.":".Config::$API_PASSWD);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    $data = array();
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $result = curl_exec($ch);  
    echo "deletion result: $result\n";
    curl_close($ch);  
}

function createResource($package,$resource,$columns,$PK,$documentation,$uri,$has_header_row,$delimiter,$start_row){
    $url = Config::$HOSTNAME . Config::$SUBDIR . $package . "/" . $resource; 
    $ch = curl_init();     
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
    curl_setopt($ch, CURLOPT_URL, $url);  
    curl_setopt($ch, CURLOPT_USERPWD, Config::$API_USER.":".Config::$API_PASSWD);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    $data = array( "resource_type" => "generic",
                   "generic_type"  => "CSV",
                   "documentation" => $documentation,
                   "uri"           => $uri,
                   "columns"       => $columns,
                   "PK"            => $PK,
                   "has_header_row"=> $has_header_row,
                   "delimiter"     => $delimiter,
                   "start_row"     => $start_row
    );
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $result = curl_exec($ch);  
    echo "creation result: $result\n";
    curl_close($ch);  
}


?>