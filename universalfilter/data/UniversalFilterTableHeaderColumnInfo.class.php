<?php

/**
 * A column in the header of the universal representation of a table
 *
 * @package The-Datatank/universalfilter/data
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jeroen Penninck
 */
class UniversalFilterTableHeaderColumnInfo {
    private $completeColumnNameParts; //array(package, package, resource, subtable, ...)
    private $columnId; //unique Id
    
    private $isLinked;
    private $linkedTable;
    private $linkedTableKey;
    
    public function __construct($completeColumnName, $isLinked=false, $linkedTable=null, $linkedTableKey=null) {
        $this->completeColumnNameParts = $completeColumnName;
        $this->isLinked = $isLinked;
        $this->linkedTable = $linkedTable;
        $this->linkedTableKey = $linkedTableKey;
        $this->columnId = uniqid();
    }
    
    public function getId(){
        return $this->columnId;
    }
    
    public function getName(){
        return $this->completeColumnNameParts[count($this->completeColumnNameParts)-1];//last
    }
    
    public function aliasColumn($newColumName){
        if(strpos($newColumName, ".")!=-1){
            $oldName = array_pop($this->completeColumnNameParts);
            $this->completeColumnNameParts[] = $newColumName;
        }else{
            throw new Exception("\"$newColumName\" is an illegal alias.");
        }
    }
    
    public function matchName($nameParts){
        $completeCount = count($this->completeColumnNameParts);
        $partCount = count($nameParts);
        if($partCount>$completeCount){
            return false;
        }
        for ($index = 0; $index < count($partCount); $index++) {
            $completePart = $this->completeColumnNameParts[$completeCount-1-$index];
            $partialPart = $nameParts[$partCount-1-$index];
            if($completePart!=$partialPart){
                return false;
            }
        }
        return true;
    }
    
    
    public function cloneColumnInfo(){
        $a = new UniversalFilterTableHeaderColumnInfo($this->completeColumnNameParts);
        $a->isLinked=$this->isLinked;
        $a->linkedTable=$this->linkedTable;
        $a->linkedTableKey=$this->linkedTableKey;
        $a->columnId=$this->columnId;
        return $a;
    }
    
    public function cloneColumnNewId(){
        $a = $this->cloneColumnInfo();
        $a->columnId = uniqid();
        return $a;
    }
    
    public function cloneBaseUpon($newFieldName){
        $a = new UniversalFilterTableHeaderColumnInfo(array($newFieldName));
        return $a;
    }
}

?>
