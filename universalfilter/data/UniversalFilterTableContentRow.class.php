<?php
/**
 * A row in the content of the universal representation of a table
 *
 * @package The-Datatank/universalfilter/data
 * @copyright (C) 2012 We Open Data
 * @license AGPLv3
 * @author Jeroen Penninck
 */
class UniversalFilterTableContentRow {
    private $data;
    
    public function __construct() {
        $this->data=new stdClass();
    }
    
    public function defineValue($idOfField, $value){
        if($idOfField=="") throw new Exception("Not a valid fieldname...");// Can happen?
        $this->data->$idOfField=array("value" => $value);
    }
    
    public function defineValueId($idOfField, $value){
        if($idOfField=="") throw new Exception("Not a valid fieldname...");// Can happen?
        $this->data->$idOfField=array("id" => $value);
    }

    public function defineGroupedValue($idOfField, $groupedColumnValues) {
        if($idOfField=="") throw new Exception("Not a valid fieldname...");// Can happen?
        $this->data->$idOfField=array("grouped" => $groupedColumnValues);
    }
    
    /**
     * returns the value of a field in the table
     */
    public function getCellValue($idOfField){
        if(isset($this->data->$idOfField)){
            $obj = $this->data->$idOfField;
            if(isset($obj["value"])){
                //var_dump($obj["value"]);
                return $obj["value"];
            }else{
                if(isset($obj["id"])){
                    return $obj["id"];
                }else{
                    if(isset($obj["grouped"])){
                        throw new Exception("Error: Can not execute this operation on a grouped field!");
                    }else{
                        //should we give a warning??? Or just return null?
                        // Can this even occure?
                        throw new Exception("Unset value for field!");
                    }
                }
            }
        }else{
            return null;
        }
    }
    
    /**
     * returns the GROUPED value of a field in the table 
     * @return array
     */
    public function getGroupedValue($idOfField){
        if(isset($this->data->$idOfField)){
            $obj = $this->data->$idOfField;
            if(isset($obj["grouped"])){
                return $obj["grouped"];
            }else{
                return null;
            }
        }else{
            return null;
        }
    }
    
    /**
     * returns a hash for the field. The hash is unique for the value!!! But does not contain special characters.
     * @param type $nameOfField
     * @return type 
     */
    public function getHashForField($idOfField){
        return $this->getCellValue($idOfField);// for now (TODO: if support recursion!)
    }
    
    /**
     * Copy the value of a column from one row to another row
     * @param UniversalFilterTableContentRow $newRow
     * @param string $oldField
     * @param string $newField 
     */
    public function copyValueTo(UniversalFilterTableContentRow $newRow, $oldField, $newField){
        $newRow->data->$newField = $this->data->$oldField;
    }
}
?>
