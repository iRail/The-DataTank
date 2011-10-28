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

    public function documentCreateRequiredParameters() {
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

    public function onCall($package, $resource) {

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
        $this->evaluateCSVResource($resource_id);

        if (!isset($this->PK)) {
            $this->PK = "";
        }

        /**
         * if no header row is given, then the columns that are being passed should be 
         * int => something, int => something
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
        }


        if ($this->columns != "") {
            parent::evaluateColumns($this->columns, $this->PK, $resource_id);
        }
    }

    private function evaluateCSVResource($resource_id) {
        if (!isset($this->has_header_row)) {
            $this->has_header_row = 1;
        }
        DBQueries::storeCSVResource($resource_id, $this->uri, $this->has_header_row);
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
                } else
                    break;
            }
            return OntologyProcessor::getInstance()->generateOntologyFromTabular($package, $resource, $fieldhash);
        } catch (Exception $ex) {
            throw new CouldNotGetDataTDTException($filename);
        }
    }

}

?>
