<?php

/**
 * This class handles a CSV file
 *
 * @package The-Datatank/custom/strategies
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */
include_once ("custom/strategies/ATabularData.class.php");

class CSV extends ATabularData {

    public static $NUMBER_OF_ITEMS_PER_PAGE = 50;
    //  the maximum execution time that a creation of a csv resource may take
    //  a csv file with 1 million lines for example will take a while to add ( will be streamed from the host )
    //  if you're experiencing timeouts, then up this variable
    public static $MAX_EXECUTION_TIME = 3000;
    public static $MAX_LINE_LENGTH = 15000;
    
    

    public function documentCreateRequiredParameters(){
        return array("uri");
    }

    //We could specify extra filters here for CSV resources
    public function documentReadRequiredParameters() {
        return array();
    }

    public function documentCreateParameters() {
        $parameters = array();
        $parameters["uri"] = "The URI to the CSV file";
        $parameters["columns"] = "An array that contains the name of the columns that are to be published, if empty array is passed every column will be published.";
        $parameters["PK"] = "The primary key of an entry";
        $parameters["has_header_row"] = "If the CSV file contains a header row with the column name, pass 1 as value, if not pass 0. Default value is 1.";
        return $parameters;
    }

    public function documentReadParameters() {
        return array();
    }

    public function readPaged($package,$resource,$page){
        /**
         * calculate which rows you must get from the paged csv resource
         * by using the NUMBER_OF_ITEMS_PER_PAGE member
         * NOTE: $page must be a >= 1 number
         */
        $fieldhash = array();
        //requested format for the (possible) next Link-header
        $format = strtolower(FormatterFactory::getInstance()->getFormat());
        if($page < 1){
            header("HTTP/1.1 303 See Other");
            header("Location: ".Config::$HOSTNAME . Config::$SUBDIR . $package."/".$resource.".$format?page=1");
            return new stdClass();
        }
        
        $upperbound = $page * CSV::$NUMBER_OF_ITEMS_PER_PAGE; 
        // SQL LIMIT clause starts with 0
        $lowerbound = $upperbound - CSV::$NUMBER_OF_ITEMS_PER_PAGE ;

        /**
         * get resulting rows
         */
        $result = DBQueries::getPagedCSVResource($package,$resource,$lowerbound,$upperbound);

        /**
         * if a null result is given, that means that the page being passed is invalid 
         */
        if(!isset($result[0])){
            throw new ParameterTDTException("There are no results for page: $page.");
        }
        
        
        $gen_res_id = $result[0]["gen_res_id"];
        
        // get the column names, note that there MUST be a published columns entry
        // for paged csv resources, for header rows are not submitted into our level 2
        // cache for these resources
        $allowed_columns = DBQueries::getPublishedColumns($gen_res_id);
        $columns = array();
        $PK ="";

        /**
         * columns can have an alias, if not their alias is their own name
         */
        foreach ($allowed_columns as $col) {
            if($col["column_name_alias"] != ""){
                $columns[(string)$col["column_name"]] = $col["column_name_alias"];
            }else{
                $columns[(string)$col["column_name"]] = $col["column_name"];
            }
            
            if ($col["is_primary_key"] == 1) {
                $PK = $columns[$col["column_name"]];
            }
        }

        // fill the fieldhash (hash of index -> columnname)
        foreach($columns as $index => $column_name){
            $fieldhash[$index] = $index;
        }

        $resultobject = array();
        $arrayOfRowObjects = array();
        $row = 0;
            
        /**
         * bouw resulting object op
         */
        foreach($result as $paged_csv_row) {
            $delimiter = $paged_csv_row["delimiter"];
            $value = $paged_csv_row["value"];
            $data = str_getcsv($value, $delimiter);
            $rowobject = new stdClass();
            $keys = array_keys($fieldhash);
                    
            for($i = 0; $i < sizeof($keys); $i++) {
                $c = $keys[$i];
                // TODO normally this if else should be reduced to the else part,
                // because there will always be a published columns entry for a paged csv resource
                if (sizeof($columns) == 0){
                    $rowobject->$c = $data[$fieldhash[$c]];
                }else if(array_key_exists($c, $columns)) {
                    $rowobject->$columns[$c] = $data[$fieldhash[$c]];
                }
            }
                    
            if ($PK == "") {
                array_push($arrayOfRowObjects, $rowobject);
            } else {
                if(!isset($arrayOfRowObjects[$rowobject->$PK])) {
                    $arrayOfRowObjects[$rowobject->$PK] = $rowobject;
                }
            }
            
        }

        /**
         * If another (next) page is available pas that one as well in the LINK header of the 
         * HTTP-message
         */
        $possible_next_page = DBQueries::getPagedCSVResource($package,$resource,$lowerbound,$upperbound);
        if(isset($possible_next_page[0])){
            $page=$page+1;
            $link = Config::$HOSTNAME .Config::$SUBDIR . $package ."/". $resource .".$format"."?page=$page";
            header("Link: $link");
        }
        return $arrayOfRowObjects;
    }

    /**
     * Read non paged resource
     */
    public function read($package, $resource) {
        /*
         * First retrieve the values for the generic fields of the CSV logic
         * This is the uri to the file, and a parameter which states if the CSV file
         * has a header row or not.
         */
        $result = DBQueries::getCSVResource($package, $resource);

        $has_header_row = $result["has_header_row"];
        $gen_res_id = $result["gen_res_id"];

        /**
         * check if the uri is valid ( not empty )
         */
        if (isset($result["uri"])) {
            $filename = $result["uri"];
        } else {
            throw new ResourceTDTException("Can't find URI of the CSV");
        }

        $columns = array();

        // get the columns from the columns table
        $allowed_columns = DBQueries::getPublishedColumns($gen_res_id);
        $PK = "";

        /**
         * columns can have an alias, if not their alias is their own name
         */
        foreach ($allowed_columns as $result) {
            if ($result["column_name_alias"] != "") {
                $columns[(string) $result["column_name"]] = $result["column_name_alias"];
            } else {
                $columns[(string) $result["column_name"]] = $result["column_name"];
            }

            if ($result["is_primary_key"] == 1) {
                $PK = $columns[$result["column_name"]];
            }
        }
        
        $resultobject = array();
        $arrayOfRowObjects = array();
        $row = 0;

        // only request public available files
        $request = TDT::HttpRequest($filename);

        if (isset($request->error)) {
            throw new CouldNotGetDataTDTException($filename);
        }
        $csv = utf8_encode($request->data);
        
        try {
            // find the delimiter
            $commas = substr_count($csv, ",", 0, strlen($csv) > 127 ? 127 : strlen($csv));
            $semicolons = substr_count($csv, ";", 0, strlen($csv) > 127 ? 127 : strlen($csv));

            $rows = str_getcsv($csv, "\n");

            $fieldhash = array();
            /**
             * loop through each row, and fill the fieldhash with the column names
             * if however there is no header, we fill the fieldhash beforehand
             * note that the precondition of the beforehand filling of the fieldhash
             * is that the column_name is an index! Otherwise there's no way of id'ing a column
             */
            if ($has_header_row == "0") {
                foreach ($columns as $index => $column_name) {
                    $fieldhash[$index] = $index;
                }
            }

            foreach ($rows as $row => $fields) {
                $data = str_getcsv($fields, $commas > $semicolons ? "," : ";");

                // keys not found yet
                if (!count($fieldhash)) {

                    // <<fast!>> way to detect empty fields
                    // if it contains empty fields, it should not be our field hash
                    $empty_elements = array_keys($data, "");
                    if (!count($empty_elements)) {
                        // we found our key fields
                        for ($i = 0; $i < sizeof($data); $i++)
                            $fieldhash[$data[$i]] = $i;
                    }
                } else {
                    $rowobject = new stdClass();
                    $keys = array_keys($fieldhash);
                    
                    for ($i = 0; $i < sizeof($keys); $i++) {
                        $c = $keys[$i];
                        
                        if (sizeof($columns) == 0 || !array_key_exists($c,$columns)) {
                            $rowobject->$c = $data[$fieldhash[$c]];
                        } else if (array_key_exists($c, $columns)) {
                            $rowobject->$columns[$c] = $data[$fieldhash[$c]];
                        }
                    }
                    
                    if ($PK == "") {
                        array_push($arrayOfRowObjects, $rowobject);
                    } else {
                        if (!isset($arrayOfRowObjects[$rowobject->$PK])) {
                            $arrayOfRowObjects[$rowobject->$PK] = $rowobject;
                        }
                    }
                }
            }

            return $arrayOfRowObjects;
        } catch (Exception $ex) {
            throw new CouldNotGetDataTDTException($filename);
        }
    }

    public function onDelete($package, $resource) {
        if(DBQueries::getIsPaged($package,$resource)){
            DBQueries::deleteCachedCSV($package,$resource);
        }
        DBQueries::deleteCSVResource($package, $resource);
    }

    public function onAdd($package_id, $generic_resource_id) {
        if(!isset($this->columns)){
            $this->columns = array();
        }

        if(!isset($this->PK)){
            $this->PK = "";
        }
        
        $columnstring = $this->implode_columns_array($this->columns);
        exec("php bin/support\ scripts/addCSV.php $package_id $generic_resource_id $this->uri $this->PK $this->has_header_row $columnstring >/dev/null 2>&1 &");
    }

    /*
     * This function will check if the CSV needs a level 2 cache or not
     * if there are more lines then $NUMBER_OF_ITEMS_PER_PAGE then we need to page
     * either way we'll update the resource entry with an is_paged value
     * NOTE: generic_resource_id is the generic_resource_csv.id 
     * Precondition: Handle has alrdy been openend, the uri works.
     */
    private function checkForPaging($rowcount,$handle,$delimiter,$generic_resource_csv_id,$resource_id){
        if($rowcount > CSV::$NUMBER_OF_ITEMS_PER_PAGE){
            DBQueries::updateIsPagedResource($resource_id,"1");
            // only read lines from the stream that are valuable to us ( so no header of commentlines )
            while (($line = fgetcsv($handle,CSV::$MAX_LINE_LENGTH, $delimiter)) !== FALSE) {
                DBQueries::insertIntoCSVCache(utf8_encode(implode($line,$delimiter)),$delimiter,$generic_resource_csv_id);
            }
        }else{
            DBQueries::updateIsPagedResource($resource_id,"0");
        }
    }
    
    private function evaluateCSVResource($gen_resource_id) {
        if (!isset($this->has_header_row)) {
            $this->has_header_row = 1;
        }
        return DBQueries::storeCSVResource($gen_resource_id, $this->uri, $this->has_header_row);
    }

    /**
     *  This function gets the fields in a resource
     * @param type $package
     * @param type $resource
     * @return type 
     */
    public function getFields($package, $resource) {

        /*
         * First retrieve the values for the generic fields of the CSV logic
         * This is the uri to the file, and a parameter which states if the CSV file
         * has a header row or not.
         */
        $result = DBQueries::getCSVResource($package, $resource);

        $has_header_row = $result["has_header_row"];
        $gen_res_id = $result["gen_res_id"];

        /**
         * check if the uri is valid ( not empty )
         */
        if (isset($result["uri"])) {
            $filename = $result["uri"];
        } else {
            throw new ResourceTDTException("Can't find URI of the CSV");
        }

        $columns = array();

        // get the columns from the columns table
        $allowed_columns = DBQueries::getPublishedColumns($gen_res_id);
        $PK = "";

        /**
         * columns can have an alias, if not their alias is their own name
         */
        foreach ($allowed_columns as $result) {
            if ($result["column_name_alias"] != "") {
                $columns[(string) $result["column_name"]] = $result["column_name_alias"];
            } else {
                $columns[(string) $result["column_name"]] = $result["column_name"];
            }

            if ($result["is_primary_key"] == 1) {
                $PK = $columns[$result["column_name"]];
            }
        }

        return array_values($columns);
    }

    private function implode_columns_array($columns){
        $columns_string = array();
        foreach($columns as $key => $val){
            array_push($columns_string,$key.";".$val);
        }
        return implode(",",$columns_string);
    }
    

}

?>
