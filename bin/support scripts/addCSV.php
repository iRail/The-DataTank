<?php
/**
 * Support script to add a CSV resource to our database back-end
 *
 * @package The-Datatank/bin/support scripts
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */

include_once("custom/strategies/CSV.class.php");
include_once("model/DBQueries.class.php");
include_once("model/ResourcesModel.class.php");
include_once("Config.class.php");
include_once('includes/rb.php');

// init the database connection
R::setup(Config::$DB,Config::$DB_USER,Config::$DB_PASSWORD);

/*
 * get package_id and generic_resource_id from arguments
 */

$package_id = $argv[1];
$generic_resource_id = $argv[2];
$uri = $argv[3];
$PK = $argv[4];
$has_header_row = $argv[5];
$implodedcolumns = $argv[6];

// rebuild the columns array from the CSV class
// it has been build by an implode of key-val pairs with ; and then again imploded with ,
$raw_columns = array();
$raw_columns = explode(",",$implodedcolumns);
$columns = array();
foreach($raw_columns as $bondedpair){
    $keyval = explode(";",$bondedpair);
    foreach($keyval as $key => $val){
        $columns[$key] = $val;
    }
}


/*
 * Create CSV entry in the back-end
 */
$generic_resource_csv_id = evaluateCSVResource($generic_resource_id,$uri,$has_header_row);
$resource_id = DBQueries::getAssociatedResourceId($generic_resource_id);

if (!isset($PK)) {
    $PK = "";
}

/**
 * if no header row is given, then the columns that are being passed should be 
 * int => something, int => something
 * if a header row is given however in the csv file, then we're going to extract those 
 * header fields and put them in our back-end as well.
 */
        
if ($has_header_row == "0") {
    // no header row ? then columns must be passed
    if(count($columns) < 1){
        $package = DBQueries::getPackageById($package_id);
        $resource = DBQueries::getResourceById($resource_id);
        ResourcesModel::getInstance()->deleteResource($package, $resource, array());
        throw new ResourceAdditionTDTException(" Your array of columns must be an index => string hash array. Since no header row is specified in the resource CSV file.");
    }
            
    foreach ($columns as $index => $value) {
        if (!is_numeric($index)) {
            $package = DBQueries::getPackageById($package_id);
            $resource = DBQueries::getResourceById($resource_id);
            ResourcesModel::getInstance()->deleteResource($package, $resource, array());
            throw new ResourceAdditionTDTException(" Your array of columns must be an index => string hash array.");
        }
    }
    $rowcount = 0;
    $commas = 0;
    $semicolons = 0;
    if (($handle = fopen($uri, "r")) !== FALSE) {
        // set timeout on 5 minutes
        stream_set_timeout($handle, CSV::$MAX_EXECUTION_TIME);
        ini_set('max_execution_time', CSV::$MAX_EXECUTION_TIME);
        while (($line = fgets($handle, CSV::$MAX_LINE_LENGTH)) !== FALSE && $rowcount < CSV::$NUMBER_OF_ITEMS_PER_PAGE) {
            $rowcount++;
            $commas = $commas + substr_count($line, ",", 0, strlen($line) > 127 ? 127 : strlen($line));
            $semicolons = $semicolons+ substr_count($line, ";", 0, strlen($line) > 127 ? 127 : strlen($line));
        }
        fclose($handle);
    }else{
        $package = DBQueries::getPackageById($package_id);
        $resource = DBQueries::getResourceById($resource_id);
        ResourcesModel::getInstance()->deleteResource($package, $resource, array());
        throw new ParameterTDTException($uri . " is not a valid URI to a file. Please make sure the link is a valid link to a CSV-file.");
                
    }

    /**
     * there is no header row, so the handle can be passed as is
     */
    $delimiter = ",";
    if($commas <  $semicolons){
        $delimiter = ";";
    }
    $fieldhash = array();
    if (($handle = fopen($uri, "r")) !== FALSE) {
        // set timeout on 5 minutes
        stream_set_timeout($handle, CSV::$MAX_EXECUTION_TIME);
        ini_set('max_execution_time', CSV::$MAX_EXECUTION_TIME);
        $checkForPaging($rowcount,$handle,$delimiter,$generic_resource_csv_id,$resource_id);
    }else{
        $package = DBQueries::getPackageById($package_id);
        $resource = DBQueries::getResourceById($resource_id);
        ResourcesModel::getInstance()->deleteResource($package, $resource, array());
        throw new ParameterTDTException($uri . " is not a valid URI to a file. Please make sure the link is a valid link to a CSV-file.");
                
    }
}else{

    /**
     * Since we don't harras the ppl with obliging them to pass along a delimiter
     * we'll have to find ourselves. In the current state we only search for 2 delimiters
     * a comma and a semicolon
     * we'll count the amount of times they occur and then derive that the most common is the delimiter.
     */
    $rowcount = 0;
    $commas = 0;
    $semicolons = 0;
    if (($handle = fopen($uri, "r")) !== FALSE) {
        // set timeout on 5 minutes
        stream_set_blocking($handle,1);
        stream_set_timeout($handle, CSV::$MAX_EXECUTION_TIME);
        ini_set('max_execution_time', CSV::$MAX_EXECUTION_TIME);
        while (($line = fgets($handle, CSV::$MAX_LINE_LENGTH)) !== FALSE && $rowcount < CSV::$NUMBER_OF_ITEMS_PER_PAGE) {
            $rowcount++;
            $commas = $commas + substr_count($line, ",", 0, strlen($line) > 127 ? 127 : strlen($line));
            $semicolons = $semicolons+ substr_count($line, ";", 0, strlen($line) > 127 ? 127 : strlen($line));
        }
        fclose($handle);
    }else{
        $package = DBQueries::getPackageById($package_id);
        $resource = DBQueries::getResourceById($resource_id);
        ResourcesModel::getInstance()->deleteResource($package, $resource, array());
        throw new ParameterTDTException($uri . " is not a valid URI to a file. Please make sure the link is a valid link to a CSV-file.");
                
    }
            
    $delimiter = ",";
    if($commas <  $semicolons){
        $delimiter = ";";
    }

    $fieldhash = array();
    if (($handle = fopen($uri, "r")) !== FALSE) {
        // set timeout on 5 minutes
        stream_set_timeout($handle, CSV::$MAX_EXECUTION_TIME);
        ini_set('max_execution_time', CSV::$MAX_EXECUTION_TIME);
        while (($line = fgetcsv($handle, CSV::$MAX_LINE_LENGTH,  $commas > $semicolons ? "," : ";")) !== FALSE) {
            // keys not found yet
            if (!count($fieldhash)) {
                // <<fast!>> way to detect empty fields
                // if it contains empty fields, it should not be our field hash
                $empty_elements = array_keys($line, "");
                if (!count($empty_elements)) {
                    // we found our key fields
                    for ($i = 0; $i < sizeof($line); $i++){
                        $fieldhash[$line[$i]] = $i;
                        $columns[$i] = $line[$i];
                    }
                    checkForPaging($rowcount,$handle,$delimiter,$generic_resource_csv_id,$resource_id);
                    break;
                }
            } 
        }
        fclose($handle);
    }else{
        $package = DBQueries::getPackageById($package_id);
        $resource = DBQueries::getResourceById($resource_id);
        ResourcesModel::getInstance()->deleteResource($package, $resource, array());
        throw new ParameterTDTException($uri . " is not a valid URI to a file. Please make sure the link is a valid link to a CSV-file.");
                
    }
}
evaluateColumns($columns, $PK, $generic_resource_id);


/*
 * This function will check if the CSV needs a level 2 cache or not
 * if there are more lines then $NUMBER_OF_ITEMS_PER_PAGE then we need to page
 * either way we'll update the resource entry with an is_paged value
 * NOTE: generic_resource_id is the generic_resource_csv.id 
 * Precondition: Handle has alrdy been openend, the uri works.
 */
function checkForPaging($rowcount,$handle,$delimiter,$generic_resource_csv_id,$resource_id){
    if($rowcount >= CSV::$NUMBER_OF_ITEMS_PER_PAGE){
        DBQueries::updateIsPagedResource($resource_id,"1");
        // only read lines from the stream that are valuable to us ( so no header of commentlines )
        while (($line = fgetcsv($handle,CSV::$MAX_LINE_LENGTH, $delimiter)) !== FALSE) {
            DBQueries::insertIntoCSVCache(utf8_encode(implode($line,$delimiter)),$delimiter,$generic_resource_csv_id);
        }
    }else{
        DBQueries::updateIsPagedResource($resource_id,"0");
    }
}
    
function evaluateCSVResource($gen_resource_id,$uri,$has_header_row) {
    if (!isset($has_header_row)) {
        $has_header_row = 1;
    }
    return DBQueries::storeCSVResource($gen_resource_id, $uri, $has_header_row);
}

function evaluateColumns($columns,$PK,$gen_res_id){
    foreach($columns as $column => $column_alias){
        DBQueries::storePublishedColumn($gen_res_id, $column,$column_alias,($PK != "" && $PK == $column?1:0));
    }
}


?>