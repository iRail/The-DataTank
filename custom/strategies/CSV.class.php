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

    public function documentCreateRequiredParameters() {
        return array("uri", "has_header_row", "delimiter");
    }

    //We could specify extra filters here for CSV resources
    public function documentReadRequiredParameters() {
        return array();
    }

    public function documentCreateParameters() {
        $parameters = array();
        $parameters["uri"] = "The URI to the CSV file";
        $parameters["columns"] = "An array that contains the name of the columns that are to be published, if empty array is passed every column will be published. Note that this parameter is not required, however if you do not have a header row, we do expect the columns to be passed along, otherwise there's no telling what the names of the columns are. This array should be build as column_name => column_alias or index => column_alias.";
        $parameters["PK"] = "The primary key of an entry. This must be the name of an existing column name in the CSV file.";
        $parameters["has_header_row"] = "If the CSV file contains a header row with the column name, pass 1 as value, if not pass 0. Default value is 1.";
        $parameters["delimiter"] = "The delimiter which is used to separate the fields that contain values.";
        $parameters["start_row"] = "The number of the row (rows start at number 1) at which the actual data starts; i.e. if the first two lines are comment lines, your start_row should be 3. Default is 1.";
        return $parameters;
    }

    public function documentReadParameters() {
        return array();
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
        $start_row = $result["start_row"];
        $delimiter = $result["delimiter"];
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
            $rows = str_getcsv($csv, "\n");
            // get rid for the comment lines according to the given start_row
            for ($i = 1; $i < $start_row; $i++) {
                array_shift($rows);
            }

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
                $data = str_getcsv($fields, $delimiter);

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

                        if (sizeof($columns) == 0 || !array_key_exists($c, $columns)) {
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

    public function onAdd($package_id, $generic_resource_id) {
        if (!isset($this->columns)) {
            $this->columns = array();
        }

        if (!isset($this->PK)) {
            $this->PK = "";
        }

        if (!isset($this->start_row)) {
            $this->start_row = 1;
        }
        
        // has_header_row should be either 1 or 0
        if($this->has_header_row != 0 && $this->has_header_row != 1){
            throw new ResourceAdditionTDTException("has_header_row must be either 1 or 0, not $this->has_header_row");
        }

        $columnstring = $this->implode_columns_array($this->columns);
        $cmd="bin/support\ scripts/addCSV.php $package_id '$generic_resource_id' '$this->uri' $this->has_header_row $this->delimiter $this->start_row '$columnstring' '$this->PK'";
        if (substr(php_uname(), 0, 7) == "Windows") {
            //Does not work, almost there
            pclose(popen("start /B C:\wamp\bin\php\php5.3.8\php " . $cmd, "r"));
        } else {
            exec("php ".$cmd . " > /dev/null 2>&1 &");
        } 
    }

    private function evaluateCSVResource($gen_resource_id) {
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

    private function implode_columns_array($columns) {
        if (empty($columns)) {
            return "-1";
        }

        $columns_string = array();
        foreach ($columns as $key => $val) {
            array_push($columns_string, $key . "/" . $val);
        }
        return implode(",", $columns_string);
    }

}

?>
