<?php

class UniversalFilterTableContentRow {
    private $data;
    
    public function __construct() {
        $this->data=new stdClass();
    }
    
    public function defineValue($nameOfField, $value){
        if($nameOfField=="") throw new Exception("Not a valid fieldname...");// Can happen?
        $this->data->$nameOfField=$value;
    }
    
    public function getValue($nameOfField){
        if(isset($this->data->$nameOfField)){
            return $this->data->$nameOfField;
        }else{
            return null;
        }
    }
}
?>
