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
$has_header_row = $argv[4];
$delimiter = $argv[5];
$start_row = $argv[6];
$implodedcolumns = $argv[7];
$PK = "";

if(isset($argv[8])){
 $PK = $argv[8];   
}

// rebuild the columns array from the CSV class
// it has been build by an implode of key-val pairs with ; and then again imploded with ,
$raw_columns = array();
if($implodedcolumns != -1){
    $raw_columns = explode(",",$implodedcolumns);
}

$columns = array();
foreach($raw_columns as $bondedpair){
    $keyval = explode("/",$bondedpair);
    $columns[$keyval[0]] = $keyval[1];
}

/*
 * Create CSV entry in the back-end
 */

$generic_resource_csv_id = evaluateCSVResource($generic_resource_id,$uri,$has_header_row,$delimiter,$start_row);
$resource_id = DBQueries::getAssociatedResourceId($generic_resource_id);

/**
 * if no header row is given, then the columns that are being passed should be 
 * int => something, int => something
 * if a header row is given however in the csv file, then we're going to extract those 
 * header fields and put them in our back-end as well.
 */
        
if ($has_header_row == "0") {
    // no header row ? then columns must be passed
    if(empty($columns)){
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
    if (($handle = fopen($uri, "r")) !== FALSE) {
        stream_set_timeout($handle, CSV::$MAX_EXECUTION_TIME);
        ini_set('max_execution_time', CSV::$MAX_EXECUTION_TIME);
        while (($line = fgets($handle, CSV::$MAX_LINE_LENGTH)) !== FALSE 
               && $rowcount < CSV::$NUMBER_OF_ITEMS_PER_PAGE + $start_row) {
            $rowcount++;
        }
        // adjust the rowcount to the number of rows that actually matter
        $rowcount = $rowcount - $start_row;
        fclose($handle);
    }else{
        $package = DBQueries::getPackageById($package_id);
        $resource = DBQueries::getResourceById($resource_id);
        ResourcesModel::getInstance()->deleteResource($package, $resource, array());
        throw new ParameterTDTException($uri . " is not a valid URI to a file. Please make sure the link is a valid link to a C SV-file.");
        
    }

    /**
     * there is no header row, so the handle can be passed as is
     */
   
    $fieldhash = array();
    if (($handle = fopen($uri, "r")) !== FALSE) {
        stream_set_timeout($handle, CSV::$MAX_EXECUTION_TIME);
        ini_set('max_execution_time', CSV::$MAX_EXECUTION_TIME);
        $commentlinecounter = 1;
        while($commentlinecounter < $start_row && ($line = fgetcsv($handle,CSV::$MAX_LINE_LENGTH, $delimiter)) !== FALSE){
            $commentlinecounter++;
        }
        checkForPaging($rowcount,$handle,$delimiter,$generic_resource_csv_id,$resource_id);
    }else{
        $package = DBQueries::getPackageById($package_id);
        $resource = DBQueries::getResourceById($resource_id);
        ResourcesModel::getInstance()->deleteResource($package, $resource, array());
        throw new ParameterTDTException($uri . " is not a valid URI to a file. Please make sure the link is a valid link to a CSV-file.");
                
    }
}else{

   
    $rowcount = 0;
    if (($handle = fopen($uri, "r")) !== FALSE) {
        stream_set_blocking($handle,1);
        stream_set_timeout($handle, CSV::$MAX_EXECUTION_TIME);
        ini_set('max_execution_time', CSV::$MAX_EXECUTION_TIME);
        while (($line = fgets($handle, CSV::$MAX_LINE_LENGTH)) !== FALSE && $rowcount < CSV::$NUMBER_OF_ITEMS_PER_PAGE + $start_row) {
            $rowcount++;
        }
        // adjust the number of rowcount to the number of rows that actually matter
        $rowcount = $rowcount - $start_row;
        fclose($handle);
    }else{
        $package = DBQueries::getPackageById($package_id);
        $resource = DBQueries::getResourceById($resource_id);
        ResourcesModel::getInstance()->deleteResource($package, $resource, array());
        throw new ParameterTDTException($uri . " is not a valid URI to a file. Please make sure the link is a valid link to a CSV-file.");
                
    }

    $fieldhash = array();
    if (($handle = fopen($uri, "r")) !== FALSE) {

        stream_set_timeout($handle, CSV::$MAX_EXECUTION_TIME);
        ini_set('max_execution_time', CSV::$MAX_EXECUTION_TIME);

        // for further processing we need to process the header row, this MUST be after the comments
        // so we're going to throw away those lines before we're processing our header_row
        // our first line will be processed due to lazy evaluation, if the start_row is the first one
        // then the first argument will return false, and being an &&-statement the second validation will not be processed
        $commentlinecounter = 1;
        while($commentlinecounter < $start_row ){
            $line = fgetcsv($handle,CSV::$MAX_LINE_LENGTH, $delimiter);
            $commentlinecounter++;
        }
       
        if(($line = fgetcsv($handle, CSV::$MAX_LINE_LENGTH,  $delimiter)) !== FALSE) {
            for ($i = 0; $i < sizeof($line); $i++){
                $fieldhash[$line[$i]] = $i;
                $columns[$i] = $line[$i];
            }
            
            checkForPaging($rowcount,$handle,$delimiter,$generic_resource_csv_id,$resource_id); 
        }else{
            $package = DBQueries::getPackageById($package_id);
            $resource = DBQueries::getResourceById($resource_id);
            ResourcesModel::getInstance()->deleteResource($package, $resource, array());
            throw new ParameterTDTException($uri . " is not a valid URI to a file. Please make sure the link is a valid link to a CSV-file.");
        }
        fclose($handle);
    }else{
        $package = DBQueries::getPackageById($package_id);
        $resource = DBQueries::getResourceById($resource_id);
        ResourcesModel::getInstance()->deleteResource($package, $resource, array());
        throw new ParameterTDTException($uri . " an error occured no more rows after row $start_row have been found.");
                
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
    
function evaluateCSVResource($gen_resource_id,$uri,$has_header_row,$delimiter,$start_row) {
    return DBQueries::storeCSVResource($gen_resource_id, $uri, $has_header_row,$delimiter,$start_row);
}

function evaluateColumns($columns,$PK,$gen_res_id){
    foreach($columns as $column => $column_alias){
        // replace whitespaces in columns by underscores
        $formatted_column = preg_replace('/\s+/','_',$column_alias);
        DBQueries::storePublishedColumn($gen_res_id, $column,$column_alias,($PK != "" && $PK == $column?1:0));
    }
}


?>