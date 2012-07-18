<?php

/**
 * Represents a list that can possibly grow very big...
 *
 * @package The-Datatank/universalfilter/interpreter/executers
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jeroen Penninck
 */
class BigList {
    public static $BLOCKSIZE = 200;
    
    private $id;
    private $size;
    
    public function __construct() {
        $this->id = uniqid();
        $size = 0;
    }
    
    public function setIndex($index, $data) {
        if($index>=$this->size){
            throw new Exception("BigList: Index out of bounds: ".$index);
        }
        $inst = BigDataBlockManager::getInstance();
        $blockindex = $index/(BigList::$BLOCKSIZE);
        $indexInBlock = $index%(BigList::$BLOCKSIZE);
        
        $oldList = $inst->get("BIGLIST_".$this->id."_".$blockindex);//load the data
        if(is_null($oldList)){
            $oldList = array();
        }
        $oldList[$indexInBlock] = $data;
        $inst->set("BIGLIST_".$this->id."_".$blockindex, $oldList);//save it again
    }
    
    public function getIndex($index) {
        if($index>=$this->size){
            throw new Exception("BigList: Index out of bounds ".$index);
        }
        $inst = BigDataBlockManager::getInstance();
        $blockindex = $index/(BigList::$BLOCKSIZE);
        $indexInBlock = $index%(BigList::$BLOCKSIZE);
        
        $oldList = $inst->get("BIGLIST_".$this->id."_".$blockindex);//load the data
        
        if(is_null($oldList)){
            $oldList = array();
        }
        return $oldList[$indexInBlock];
    }
    
    public function addItem($data){
        $this->size++;
        $this->setIndex($this->size-1, $data);
    }
    
    public function getSize(){
        return $this->size;
    }
}

?>
