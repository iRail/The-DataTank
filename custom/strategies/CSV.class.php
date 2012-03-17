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
        return array("uri");
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
        $this->parameters["uri"] = "The URI to the CSV file";
        $this->parameters["PK"] = "The primary key of an entry. This must be the name of an existing column name in the CSV file.";
        $this->parameters["has_header_row"] = "If the CSV file contains a header row with the column name, pass 1 as value, if not pass 0. Default value is 1.";
        $this->parameters["delimiter"] = "The delimiter which is used to separate the fields that contain values, default value is a semicolon.";
        $this->parameters["start_row"] = "The number of the row (rows start at number 1) at which the actual data starts; i.e. if the first two lines are comment lines, your start_row should be 3. Default is 1.";
        return $this->parameters;
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
    public function read(&$configObject) {
        /*
         * First retrieve the values for the generic fields of the CSV logic
         * This is the uri to the file, and a parameter which states if the CSV file
         * has a header row or not.
         */
        parent::read($configObject);
        $has_header_row = $configObject->has_header_row;
        $start_row = $configObject->start_row;
        $delimiter = $configObject->delimiter;
        /**
         * check if the uri is valid ( not empty )
         */
        if (isset($configObject->uri)) {
            $filename = $configObject->uri;
        } else {
            throw new ResourceTDTException("Can't find URI of the CSV");
        }
      
        $columns = $configObject->columns;
        $PK = $configObject->PK;

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
    
    protected function isValid($package_id,$generic_resource_id) {

        if (!isset($this->columns)) {
            $this->columns = array();
        }

        if(!isset($this->has_header_row)){
            $this->has_header_row = 1;
        }

        if (!isset($this->PK)) {
            $this->PK = "";
        }

        if(!isset($this->delimiter)){
            $this->delimiter = ";";
        }
       
        if (!isset($this->start_row)) {
            $this->start_row = 1;
        }
        
        // has_header_row should be either 1 or 0
        if($this->has_header_row != 0 && $this->has_header_row != 1){
            $this->throwException($package_id,$generic_resource_id, "Header row should be either 1 or 0.");
        }

        /**
         * if no header row is given, then the columns that are being passed should be 
         * int => something, int => something
         * if a header row is given however in the csv file, then we're going to extract those 
         * header fields and put them in our back-end as well.
         */
        
        if ($this->has_header_row == "0") {
            // no header row ? then columns must be passed
            if(empty($this->columns)){
                $this->throwException($package_id,$generic_resource_id,"Your array of columns must be an index => string hash array. Since no header row is specified in the resource CSV file.");
            }
            
            foreach ($this->columns as $index => $value) {
                if (!is_numeric($index)) {
                    $this->throwException($package_id,$generic_resource_id,"Your array of columns must be an index => string hash array.");
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
                    $this->throwException($package_id,$generic_resource_id,$this->uri . " is not a valid URI to a file. Please make sure the link is a valid link to a CSV-file.");
                }
                fclose($handle);
            }else{
                $this->throwException($package_id,$generic_resource_id,$this->uri . " an error occured no more rows after row $start_row have been found.");
            }
        }
        return true;
    }
}
?>
