<?php

/**
 * Description of PhpObjectTableConverter
 *
 * @author Jeroen
 */
class PhpObjectTableConverter {
    
    public static $ID_KEY="key_";
    
    
    /**
     * Gets the resource as a php object
     * @param type $package
     * @param type $resource
     * @return type phpObject
     */
    private function getFullResourcePhpObject($package, $resource){
        $resourceObject = ResourcesModel::getInstance()->readResource($package, $resource, array(), array());
        
        //implement cache
        
        return $resourceObject;
    }
    
    /**
     * Finds all paths from $root by following the fields with names in $path
     * (Splits on arrays)
     * 
     * @param type $root
     * @param type $path 
     */
    private function findTablePhpArray($root, $path){
        if(!empty($path)){
            $fieldToSearch = array_shift($path);
            
            if(isset($root[$fieldToSearch])){
                $fieldvalue = $root[$fieldToSearch];
                if(is_array($fieldvalue)){
                    $combined=array();
                    foreach($fieldvalue as $obj){
                        $combined = array_merge($combined, findTablePhpArray($obj, $path));
                    }
                    return $combined;
                }else if(is_object($fieldvalue)){
                    return findTablePhpArray($fieldvalue, $path);
                }else{
                    return array();
                }
            }else{
                return array();
            }
        }else{
            if(is_object($root)){
                return array($root);
            }else if(is_array($root)){
                return $root;
            }else{
                $obj=new stdClass();
                $obj->text=$root;
                return array($obj);
            }
        }
    }
    
    private function getPhpObjectsByIdentifier($splitedId){
        $resource = $this->getFullResourcePhpObject($splitedId[0], $splitedId[1]);
        
        $phpObj = $this->findTablePhpArray($resource, $splitedId[2]);
        
        return $phpObj;
    }
    
    private function parseColumnName($name){
        return preg_replace("/[^A-Za-z0-9]/", "_", $name);
    }
    
    private function getPhpObjectTableHeader($nameOfTable, $objects){
        $columnNames = array();
        $tableLinks = array();
        
        foreach($objects as $obj){
            $arr_obj = get_object_vars($obj);
            foreach($arr_obj as $key => $value){
                $columnName=$this->parseColumnName($key);
                if(!in_array($columnName, $columnNames)){
                    array_push($columnNames, $columnName);
                    if(is_array($value) || is_object($value)){
                        $linkInfo = new stdClass();
                        $linkInfo->table=$totalId.".".$columnName;
                        $linkInfo->key=PhpObjectTableConverter::$ID_KEY+$nameOfTable;//todo: check first if field does not exists...
                        
                        array_push($tableLinks, $linkInfo);
                    }
                }
            }
            //todo: add id field
        }
        
        $header = new UniversalFilterTableHeader($columnNames, $tableLinks, false, false);
        
        return $header;
    }
    
    private function getPhpObjectTableContent($nameOfTable, $objects){
        $rows=array();
        
        foreach($objects as $obj){
            $arr_obj = get_object_vars($obj);
            $currentrow=new UniversalFilterTableContentRow();
            foreach($arr_obj as $key => $value){
                $columnName=$this->parseColumnName($key);
                $currentrow->defineValue($columnName, $value);
            }
            //todo: add id field
            //todo: loop through header, and add key-fields
            array_push($rows, $currentrow);
        }
        
        $content = new UniversalFilterTableContent($rows);
        
        return $content;
    }
    
    public function getPhpObjectTable($totalId, $splitedId){
        $objects = $this->getPhpObjectsByIdentifier($splitedId);
        
        $nameOfTable=$splitedId[1];
        if(count($splitedId[2])>0){
            $nameOfTable=$splitedId[2][count($splitedId[2])-1];
        }
        
        $header = $this->getPhpObjectTableHeader($nameOfTable, $objects);
        
        $body = $this->getPhpObjectTableContent($nameOfTable, $objects);
        
        return new UniversalFilterTable($header, $body);
    }
}

?>
