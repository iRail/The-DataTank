<?php

/**
 * This class handles a CSV file
 *
 * @package The-Datatank/model/resources/strategies
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */
include_once ("model/resources/strategies/ATabularData.class.php");

class CSV extends ATabularData {

    private $NUMBER_OF_ITEMS_PER_PAGE = 50;

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
        if($page < 1){
            throw new ParameterTDTException("The pagenumber must be equal or larger than 1");
        }
        
        $upperbound = $page * $this->NUMBER_OF_ITEMS_PER_PAGE; 
        // SQL LIMIT clause starts with 0
        $lowerbound = $upperbound - $this->NUMBER_OF_ITEMS_PER_PAGE + 1;
        
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
         * NOTE: The requested format is only known in the RController, to pass this along all the way down to 
         * the strategy is quite absurd, so we're going to put .about, and then change it later on in the RController
         */
        $possible_next_page = DBQueries::getPagedCSVResource($package,$resource,$lowerbound,$upperbound);
        if(isset($possible_next_page[0])){
            $page=$page+1;
            $link = Config::$HOSTNAME . $package ."/". $resource .".about"."?page=$page";
            header("Link: $link");
        }
        return $arrayOfRowObjects;
    }

    /**
     * Read non paged resource
     */
    public function readNonPaged($package, $resource) {
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
                        if (sizeof($columns) == 0) {
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
        DBQueries::deleteCSVResource($package, $resource);
    }

    public function onAdd($package_id, $resource_id) {
        /*
         * Create CSV entry in the back-end
         */
        $generic_resource_id = $this->evaluateCSVResource($resource_id);

        if (!isset($this->PK)) {
            $this->PK = "";
        }

        /**
         * if no header row is given, then the columns that are being passed should be 
         * int => something, int => something
         * if a header row is given however in the csv file, then we're going to extract those 
         * header fields and put them in our back-end as well.
         */
        if (!isset($this->columns)) {
            $this->columns = "";
        }
        if ($this->has_header_row == "0") {
            foreach ($this->columns as $index => $value) {
                if (!is_numeric($index)) {
                    $package = DBQueries::getPackageById($package_id);
                    $resource = DBQueries::getResourceById($resource_id);
                    ResourcesModel::getInstance()->deleteResource($package, $resource, array());
                    throw new ResourceAdditionTDTException(" Your array of columns must be an index => string hash array.");
                }
            }
            /**
             * check if data should be paged
             */
            $request = TDT::HttpRequest($this->uri);
            
            if (isset($request->error)) {
                throw new CouldNotGetDataTDTException($this->uri);
            }

            $csv = utf8_encode($request->data);
            
            /**
             * find the delimiter
             */
            $commas = substr_count($csv, ",", 0, strlen($csv) > 127 ? 127 : strlen($csv));
            $semicolons = substr_count($csv, ";", 0, strlen($csv) > 127 ? 127 : strlen($csv));
            
            $delimiter = ",";
            if($commas <  $semicolons){
                $delimiter = ";";
            }
            $rows = str_getcsv($csv,"\n");
            /**
             * there is no header row, so the rows can be passed as is
             */
            $this->checkForPaging($rows,$delimiter,$generic_resource_id,$resource_id);
        }else{
            // find header fields, then check for paging
            $request = TDT::HttpRequest($this->uri);
            
            if (isset($request->error)) {
                throw new CouldNotGetDataTDTException($this->uri);
            }

            $csv = utf8_encode($request->data);
            
            try {
                $fieldhash = array();
                // find the delimiter
                $commas = substr_count($csv, ",", 0, strlen($csv) > 127 ? 127 : strlen($csv));
                $semicolons = substr_count($csv, ";", 0, strlen($csv) > 127 ? 127 : strlen($csv));

                $delimiter = ",";
                if($commas <  $semicolons){
                    $delimiter = ";";
                }
                /**
                 * strip the rows that are not meaningfull (rows before the header + header itself)
                 */
                $rows = str_getcsv($csv, "\n");
                $rows_backup = $rows;
                foreach ($rows as $row => $fields) {
                    $data = str_getcsv($fields, $commas > $semicolons ? "," : ";");
                    // keys not found yet
                    if (!count($fieldhash)) {
                        array_shift($rows);
                        // <<fast!>> way to detect empty fields
                        // if it contains empty fields, it should not be our field hash
                        $empty_elements = array_keys($data, "");
                        if (!count($empty_elements)) {
                            // we found our key fields
                            for ($i = 0; $i < sizeof($data); $i++){
                                $fieldhash[$data[$i]] = $i;
                                $this->columns[$i] = $data[$i];
                            }
                        }
                    } else
                        break;
                }
            } catch (Exception $ex) {
                throw new CouldNotGetDataTDTException($this->uri);
            }
            $this->checkForPaging($rows,$delimiter,$generic_resource_id,$resource_id);
        }
        $columns = explode(";",$this->columns);
        parent::evaluateColumns($columns, $this->PK, $resource_id);
    }
    
    /*
     * This function will check if the CSV needs a level 2 cache or not
     * if there are more lines then $NUMBER_OF_ITEMS_PER_PAGE then we need to page
     * either way we'll update the resource entry with an is_paged value
     * NOTE: generic_resource_id is the generic_resource_csv.id 
     */
    private function checkForPaging($rows,$delimiter,$generic_resource_id,$resource_id){
        if(count($rows) > $this->NUMBER_OF_ITEMS_PER_PAGE){
            DBQueries::updateIsPagedResource($resource_id,"1");
            foreach($rows as $row => $fields){
                DBQueries::insertIntoCSVCache($fields,$delimiter,$generic_resource_id);
            }
        }else{
            DBQueries::updateIsPagedResource($resource_id,"0");
        }
        
    }

    private function evaluateCSVResource($resource_id) {
        if (!isset($this->has_header_row)) {
            $this->has_header_row = 1;
        }
        return DBQueries::storeCSVResource($resource_id, $this->uri, $this->has_header_row);
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

}

?>
