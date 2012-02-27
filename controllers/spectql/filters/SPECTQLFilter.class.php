<?php
/**
 * Implements a filter: ?
 *
 * @package The-Datatank/controllers/spectql/filters
 * @copyright (C) 2011 by OKFN chapter Belgium vzw/asbl
 * @license LGPL
 * @author Pieter Colpaert
 * @organisation Hogent
 */
class SPECTQLFilter{
    private $key,$operation,$value;

    public function __construct($key, $operation, $value){
        $this->key = $key;
        $this->operation = $operation;
        $this->value = $value;
    }
    
    private $functions = array(
        ">" => "greaterthan",
        "<" => "lessthan",
        "==" => "equals",
        "~" => "like"
    );
    
    
    public function execute(&$current){
        $function = $this->functions[$this->operation];

        if(!is_array($current)){
            throw new ParserTDTException("The resource you specified is not a resource we can filter");
        }

        $result = array();
        foreach($current as &$row){
            if(isset($row[$this->key]) && $function($row[$this->key],$this->value)){
                array_push($result,$row);
            }
        }
        $current = $result;
    }
}

//Specify some comparators
function equals($a,$b){
    return strnatcmp($a,$b) == 0;
}
function greaterthan($a,$b){
    return strnatcmp($a,$b) > 0;
}
function lessthan($a,$b){
    return strnatcmp($a,$b) < 0;
}
function like($a,$b){
    return strstr($a,$b);
}

?>
