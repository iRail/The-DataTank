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

    // amount of chars in one row that can be read
    public static $MAX_LINE_LENGTH = 15000;

    /**
     * Returns an array with params => documentation pairs who are required to create a CSV resource.
     * @return array with parameter => documentation pairs
     */
    public function documentCreateRequiredParameters() {
        return array("uri", "has_header_row", "delimiter");
    }

    /**
     * Document all the read required parameters for documentation purposes. 
     * @return array with necessary parameters to read a CSV.
     */
    public function documentReadRequiredParameters() {
        return array();
    }

    /**
     * Returns an array with params => documentation pairs that can be used to create a CSV resource.
     * @return array with parameter => documentation pairs
     */
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

    /**
     * Returns an array with parameter => documentation pairs that can be used to read a CSV resource.
     * @return array with parameter => documentation pairs
     */
    public function documentReadParameters() {
        return array();
    }
    
    /**
     * Read a resource
     * @param $package The package name of the resource 
     * @param $resource The resource name of the resource
     * @return $mixed An object created with fields of a CSV file.
     */
    public function read($package, $resource) {
        /*
         * First retrieve the values for the generic fields of the CSV logic
         * This is the uri to the file, and a parameter which states if the CSV file
         * has a header row or not.
         */
        $result = $this->getCSVResource($package, $resource);
    
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
                
            if(count($data) != count($columns)){
                throw new ReadTDTException("The amount of columns and data from the csv don't match up, this could be because an incorrect delimiter has been passed.");
            }
                
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
    }

    public function onDelete($package, $resource) {
        $this->deleteCSVResource($package, $resource);
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

        /*
         * Create CSV entry in the back-end
         */
        $generic_resource_csv_id = $this->storeCSVResource($generic_resource_id,$this->uri,$this->has_header_row,$this->delimiter,$this->start_row);
        $resource_id = DBQueries::getAssociatedResourceId($generic_resource_id);

        /**
         * if no header row is given, then the columns that are being passed should be 
         * int => something, int => something
         * if a header row is given however in the csv file, then we're going to extract those 
         * header fields and put them in our back-end as well.
         */
        
        if ($this->has_header_row == "0") {
            // no header row ? then columns must be passed
            if(empty($this->columns)){
                $this->package = DBQueries::getPackageById($package_id);
                $this->resource = DBQueries::getResourceById($resource_id);
                ResourcesModel::getInstance()->deleteResource($this->package, $this->resource, array());
                throw new ResourceAdditionTDTException(" Your array of columns must be an index => string hash array. Since no header row is specified in the resource CSV file.");
            }
            
            foreach ($this->columns as $index => $value) {
                if (!is_numeric($index)) {
                    $this->package = DBQueries::getPackageById($package_id);
                    $this->resource = DBQueries::getResourceById($resource_id);
                    ResourcesModel::getInstance()->deleteResource($this->package, $this->resource, array());
                    throw new ResourceAdditionTDTException(" Your array of columns must be an index => string hash array.");
                }
            }

        }else{
            $fieldhash = array();
            if (($handle = fopen($this->uri, "r")) !== FALSE) {

                // for further processing we need to process the header row, this MUST be after the comments
                // so we're going to throw away those lines before we're processing our header_row
                // our first line will be processed due to lazy evaluation, if the start_row is the first one
                // then the first argument will return false, and being an &&-statement the second validation will not be processed
                $commentlinecounter = 1;
                while($commentlinecounter < $this->start_row ){
                    $line = fgetcsv($handle,CSV::$MAX_LINE_LENGTH, $this->delimiter);
                    $commentlinecounter++;
                }
       
                if(($line = fgetcsv($handle, CSV::$MAX_LINE_LENGTH,  $this->delimiter)) !== FALSE) {
                    // if no column aliases have been passed, then fill the columns variable 
                    if(empty($this->columns)){                        
                        for ($i = 0; $i < sizeof($line); $i++){
                            $fieldhash[$line[$i]] = $i;
                            $this->columns[$i] = $line[$i];
                        }
                    }
                }else{
                    $this->package = DBQueries::getPackageById($package_id);
                    $this->resource = DBQueries::getResourceById($resource_id);
                    ResourcesModel::getInstance()->deleteResource($this->package, $this->resource, array());
                    throw new ResourceAdditionTDTException($this->uri . " is not a valid URI to a file. Please make sure the link is a valid link to a CSV-file.");
                }
                fclose($handle);
            }else{
                $this->package = DBQueries::getPackageById($package_id);
                $this->resource = DBQueries::getResourceById($resource_id);
                ResourcesModel::getInstance()->deleteResource($this->package, $this->resource, array());
                throw new ResourceAdditionTDTException($this->uri . " an error occured no more rows after row $start_row have been found.");
                
            }
        }
        $this->evaluateColumns($this->columns, $this->PK, $generic_resource_id);
    }

    private function evaluateCSVResource($gen_resource_id) {
        return $this->storeCSVResource($gen_resource_id, $this->uri, $this->has_header_row);
    }

    /**
     *  This function gets the fields in a resource
     * @param string $package
     * @param string $resource
     * @return array Array with column names mapped onto their aliases
     */
    public function getFields($package, $resource) {
        /*
         * First retrieve the values for the generic fields of the CSV logic
         * This is the uri to the file, and a parameter which states if the CSV file
         * has a header row or not.
         */
        $result = $this->getCSVResource($package, $resource);
        $gen_res_id = $result["gen_res_id"];

        $columns = array();

        // get the columns from the columns table
        $allowed_columns = DBQueries::getPublishedColumns($gen_res_id);

        /**
         * columns can have an alias, if not their alias is their own name
         */
        foreach ($allowed_columns as $result) {
            if ($result["column_name_alias"] != "") {
                $columns[(string) $result["column_name"]] = $result["column_name_alias"];
            } else {
                $columns[(string) $result["column_name"]] = $result["column_name"];
            }
        }
        return array_values($columns);
    }

    /**
     * store the columns
     */
    protected function evaluateColumns($columns,$PK,$gen_res_id){
        // check if PK is in the column keys
        if($PK != "" && !array_key_exists($PK,$columns)){
            throw new ResourceAdditionTDTException($PK ." as a primary key is not one of the column name keys. Either leave it empty or name it after a column name (not a column alias).");
        }
        
        foreach($columns as $column => $column_alias){
            // replace whitespaces in columns by underscores
            $formatted_column = preg_replace('/\s+/','_',$column_alias);
            DBQueries::storePublishedColumn($gen_res_id, $column,$column_alias,($PK != "" && $PK == $column?1:0));
        }
    }


/*
******************
**** QUERIES *****
******************
/
/**
* Get a generic resource id and the generic resource csv id
* given a package and a a resource
*/
    private function getCSVInfo($package,$resource){
        return R::getRow("SELECT generic_resource.id as gen_id, generic_resource_csv.id as csv_id,delimiter,start_row
                          FROM package,resource,generic_resource,generic_resource_csv
                          WHERE package_name=:package and resource_name=:resource and package_id = package.id
                                and generic_resource.resource_id=resource.id and gen_resource_id = generic_resource.id",
                         array(":package" => $package, ":resource" => $resource)
        );
    }

    /**
     * Store a CSV resource
     */
    private function storeCSVResource($resource_id, $uri,$has_header_row,$delimiter,$start_row){
        $resource = R::dispense("generic_resource_csv");
        $resource->gen_resource_id = $resource_id;
        $resource->uri = $uri;
        $resource->has_header_row = $has_header_row;
        $resource->start_row = $start_row;
        $resource->delimiter = $delimiter;
        return R::store($resource);
    }

    /**
     * Delete a specific CSV resource
     */
    private function deleteCSVResource($package, $resource) {
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
     * Retrieve a specific CSV resource
     */
    static function getCSVResource($package, $resource) {
        return R::getRow(
            "SELECT generic_resource.id as gen_res_id,generic_resource_csv.uri as uri,
                    generic_resource_csv.has_header_row as has_header_row, start_row,delimiter
             FROM  package,resource, generic_resource, generic_resource_csv
             WHERE package.package_name=:package and resource.resource_name=:resource
                   and package.id=resource.package_id 
                   and resource.id = generic_resource.resource_id
                   and generic_resource.id=generic_resource_csv.gen_resource_id",
            array(':package' => $package, ':resource' => $resource)
        );
    }
}
?>
