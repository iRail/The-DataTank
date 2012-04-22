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
    public static $PAGE_SIZE = 2;
    /**
     * Returns an array with params => documentation pairs who are required to create a CSV resource.
     * @return array with parameter => documentation pairs
     */
    public function documentCreateRequiredParameters() {
        return array("uri");
    }

    /**
     * Returns an array with params => documentation pairs that can be used to update a CSV resource.
     * @return array with parameter => documentation pairs
     */
    public function documentUpdateParameters(){
        $this->parameters["uri"] = "The URI to the CSV file.";
        $this->parameters["PK"] = "The primary key of an entry. This must be the name of an existing column name in the CSV file.";
        $this->parameters["has_header_row"] = "If the CSV file contains a header row with the column name, pass 1 as value, if not pass 0. Default value is 1.";
        $this->parameters["delimiter"] = "The delimiter which is used to separate the fields that contain values, default value is a comma.";
        $this->parameters["start_row"] = "The number of the row (rows start at number 1) at which the actual data starts; i.e. if the first two lines are comment lines, your start_row should be 3. Default is 1.";
        return $this->parameters;
    }

    /**
     * Returns an array with params => documentation pairs that can be used to create a CSV resource.
     * @return array with parameter => documentation pairs
     */
    public function documentCreateParameters() {
        $this->parameters["uri"] = "The URI to the CSV file.";
        $this->parameters["PK"] = "The primary key of an entry. This must be the name of an existing column name in the CSV file.";
        $this->parameters["has_header_row"] = "If the CSV file contains a header row with the column name, pass 1 as value, if not pass 0. Default value is 1.";
        $this->parameters["delimiter"] = "The delimiter which is used to separate the fields that contain values, default value is a comma.";
        $this->parameters["start_row"] = "The number of the row (rows start at number 1) at which the actual data starts; i.e. if the first two lines are comment lines, your start_row should be 3. Default is 1.";
        return $this->parameters;
    }

    /**
     * Returns an array with parameter => documentation pairs that can be used to read a CSV resource.
     * @return array with parameter => documentation pairs
     */
    public function documentReadParameters() {
        return array("startindex" => "The startrow of the datasource you want as a starting point of your response. Do note that this is the rownumber starting(!!) from the start_row passed with the creation of the resource.",
                     "endindex" => "The endrow of the datasource you want in the response. Note that a startindex must be passed in order to use the endindex parameter.",
                     "page" => "The page of the datasource you want to access, paging is done on an internal parameter, currently set to " . CSV::$PAGE_SIZE ." rows are on 1 page. You can only use page, when no index is passed as parameter.");
    }
    
    /**
     * Read a resource
     * @param $package The package name of the resource 
     * @param $resource The resource name of the resource
     * @return $mixed An object created with fields of a CSV file.
     */
    public function read(&$configObject,$package,$resource) {

        /**
         * Check if the possible read parameters are passed in a correct way
         */

        if(isset($this->startindex) && isset($this->endindex) && $this->endindex < $this->startindex){
            throw new ParameterTDTException("Your endindex cannot be smaller than your startindex.");
        }else if(isset($this->page) && isset($this->startindex) || isset($this->endindex)){
            throw new ParameterTDTException("The usage of page and indexes are mutually exclusive.");
        }else if(isset($this->endindex) && !isset($this->startindex)){
            throw new ParameterTDTException("You cannot use endindex without a startindex.");
        }

        /**
         * convert page to startindex and endindex
         */
        if(isset($this->page)){
            $this->startindex = $this->page * CSV::$PAGE_SIZE;
            $this->endindex = $this->startindex + CSV::$PAGE_SIZE;
        }

        if(!isset($this->startindex)){
            $this->startindex = 1;
        }
        

        parent::read($configObject,$package,$resource);
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

        if(!isset($this->endindex)){
            $this->endindex = count($rows);
        }
        
        // get rid for the comment lines according to the given start_row & beginindex
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
        }else{
            // <<fast!>> way to detect empty fields
            // if it contains empty fields, it should not be our field hash
            $data = str_getcsv($rows[0],$delimiter, '"');
            $empty_elements = array_keys($data, "");
            if (!count($empty_elements)){
                // we found our key fields
                for ($i = 0; $i < sizeof($data); $i++)
                    $fieldhash[$data[$i]] = $i;
                array_shift($rows);
            }else{
                throw new ReadTDTException("The columns couldn't be resolved, line which, according to the parameters, holds the columns is: ". implode($delimiter,$data));
            }
        }

        /**
         * throw away the rows we dont need (startindex)
         */
        // get rid for the comment lines according to the given start_row & beginindex
        for ($i = 1; $i < $this->startindex; $i++) {
            array_shift($rows);
        }

        $rownumber = 1;
        foreach ($rows as $row => $fields) {     
            /**
             * Check if we have to end the reading ( don't push over endindex )
             */
            
            if($this->endindex - $this->startindex + 1  == $rownumber){
                if($rownumber < count($rows)){
                    if(!isset($this->page)){
                        $start = $rownumber+1;
                        header("Link: " . Config::$HOSTNAME . Config::$SUBDIR . $package . "/" . $resource .".about?beginindex=".$start);
                    }else{
                        $page = $this->page + 1;
                        header("Link: ". Config::$HOSTNAME . Config::$SUBDIR . $package . "/" . $resource .".about?page=".$page);
                    }
                }
                break;
            }

            $data = str_getcsv($fields, $delimiter, '"');
                
            // check if the delimiter exists in the csv file ( comes down to checking if the amount of fields in $data > 1 )
            if(count($data)<=1){
                throw new ReadTDTException("The delimiter ( " . $delimiter . " ) wasn't present in the file, re-add the resource with the proper delimiter.");
            }
            
            /**
             * We support sparse trailing (empty) cells 
             * 
             */
            if(count($data) != count($columns)){ 
                if(count($data) < count($columns)){ 
                    /**
                     * trailing empty cells
                     */
                    $missing = count($columns) - count($data);
                    for ($i = 0; $i < $missing; $i++){
                        $data[] = "";
                    }                    
                }else if(count($data) > count($columns)){
                    $line = $start_row + $this->startindex + $rownumber ;
                    throw new ReadTDTException("The amount of data columns is larger than the amount of header columns from the csv, this could be because an incorrect delimiter (". $delimiter .") has been passed, or a corrupt datafile has been used. Line number of the error: $line.");
                }
            }

            
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
                if (!isset($arrayOfRowObjects[$rowobject->$PK]) && $rowobject->$PK != "") {
                    $arrayOfRowObjects[$rowobject->$PK] = $rowobject;
                }
            }
            $rownumber++;
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
            $this->delimiter = ",";
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
                    $line = fgetcsv($handle,CSV::$MAX_LINE_LENGTH, $this->delimiter,'"');
                    $commentlinecounter++;
                }

                if(($line = fgetcsv($handle, CSV::$MAX_LINE_LENGTH,  $this->delimiter,'"')) !== FALSE) {
                    // if no column aliases have been passed, then fill the columns variable 
                    if(count($line) <= 1){
                        throw new ResourceAdditionTDTException("The delimiter ( ".$this->delimiter. " ) wasn't found in the first line of the file, perhaps the file isn't a CSV file or you passed along a wrong delimiter.");
                    }
                    
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
