<?php
/**
 * Implements a selector: {...}
 *
 * @package The-Datatank/controllers/spectql/selectors
 * @copyright (C) 2011 by OKFN chapter Belgium vzw/asbl
 * @license LGPL
 * @author Pieter Colpaert
 * @organisation Hogent
 */

class SPECTQLSelector{
    
    private $arguments;
    //hash array of elements to sort on. key contains the argumentname, value contains the order
    private $order;

    public function __construct($firstargument, $order = 0){
        $this->arguments[] = $firstargument;
        if($order !=0){
            $this->order[$firstargument->getName()] = $order;
        }
    }

    public function execute($resource){
        //arr are the resource values
	$arr = $resource->execute(); //returns a hash of values
        $result = array();
        //each result is made column per column
        foreach($this->arguments as $argument){
            //every argument will take 2 arguments: the result so far, and the entire resource
            $argument->execute($result,$arr);
        }
        //all that is left is to order everything according to the flags we've set in $this->order
        $this->sortResource($result);
        return $result;
    }
    
    public function addArgument($argument, $order = 0){
        $this->arguments[] = $argument;
        if($order !=0){
            $this->order[$argument->getName()] = $order;
        }
    }

    private function sortResource(&$resource){
        if(sizeof($this->order)>0){
            usort($resource,$this->makeSortFunction());
        }
    }

    private function makeSortFunction(){
        $arraycode = "array(";
        foreach($this->order as $key => $val){
            $arraycode .="\"". $key . "\"=>\"" . $val . "\",";
        }
        $arraycode .= ")";
        $code = '
            $orderlist ='. $arraycode .';
            $keys = array_keys($orderlist);
            $i = 0;
            $result = SPECTQLSelector::compareRow($a,$b, $keys[$i], $orderlist[$keys[$i]]);
            while($i < sizeof($keys) && $result == 0){
                $result = SPECTQLSelector::compareRow($a,$b,$keys[$i], $orderlist[$keys[$i]]);
                $i++;
            }
            return $result;
            ';
        return create_function('$a,$b', $code);
    }

    public static function compareRow(&$a,&$b,$key,$order){
        $result = 0;
        if(!isset($a[$key]) && !isset($b[$key])){
            $result = 0;
        }else if(!isset($a[$key])){
            $result = -1;
        }else if(!isset($b[$key])){
            $result = 1;
        }
        if(is_object($a[$key]) || is_array($a[$key])){
            throw new ParserTDTException("Cannot sort on an object or array. Please give us a string to sort on.");
        }
        $result = strnatcmp($a[$key], $b[$key]);
        return $result * $order;
    }
}

?>
