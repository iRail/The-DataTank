<?php

include_once("universalfilter/common/BigDataBlockManager.class.php");
include_once("universalfilter/common/BigList.class.php");
include_once("universalfilter/common/BigMap.class.php");

include_once("universalfilter/data/UniversalFilterTableContentRow.class.php");
include_once("universalfilter/data/UniversalFilterTableHeader.class.php");
include_once("universalfilter/data/UniversalFilterTableContent.class.php");
include_once("universalfilter/data/UniversalFilterTable.class.php");
include_once("universalfilter/data/UniversalFilterTableHeaderColumnInfo.class.php");

include_once("universalfilter/tablemanager/tools/PhpObjectTableConverter.class.php");

/**
 * The TableManager makes it easier to view The DataTank as a collection of tables
 *
 * @package The-Datatank/universalfilter/tablemanager
 * @copyright (C) 2012 We Open Data
 * @license AGPLv3
 * @author Jeroen Penninck
 */
class UniversalFilterTableManager {
    private static $IDENTIFIERSEPARATOR=".";
    
    private $resourcesmodel;
    
    public function __construct() {
        $this->resourcesmodel = ResourcesModel::getInstance();
    }
    
    /**
     * Splits the identifier in 3 pieces:
     *  - a package (string)
     *  - a resource (string)
     *  - a array of subidentifiers
     * 
     * @param type $globalTableIdentifier string => see universal/UniversalFilters.php/Identifier for format
     * @return type array of the three pieces
     */
    private function splitIdentifier($globalTableIdentifier){
        $identifierpieces=explode(UniversalFilterTableManager::$IDENTIFIERSEPARATOR,$globalTableIdentifier);
        
        $packageDot=array_shift($identifierpieces);
        $package=$packageDot;
        
        while(!$this->resourcesmodel->hasPackage($package) && !empty($identifierpieces)){
            $firstitem=array_shift($identifierpieces);
            $packageDot.=UniversalFilterTableManager::$IDENTIFIERSEPARATOR.$firstitem;
            $package.="/".$firstitem;
        }
        
        if($this->resourcesmodel->hasPackage($package)){
            $resourcename = array_shift($identifierpieces);
            if($this->resourcesmodel->hasResource($package, $resourcename)){
                return array($package,$resourcename,$identifierpieces);
            }else{
                throw new ResourceOrPackageNotFoundTDTException("Illegal identifier. Package does not contain a resourcename.");
            }
        }else{
            throw new ResourceOrPackageNotFoundTDTException("Illegal identifier. Identifier does not contain a packagename.");
        }
    }
    
    public function getTableHeader($globalTableIdentifier){
        $splitedId = $this->splitIdentifier($globalTableIdentifier);
        // TODO: do optimalisation here (if object supports getHeader itself)
        
        $converter = new PhpObjectTableConverter();
        
        $table = $converter->getPhpObjectTable($globalTableIdentifier, $splitedId);
        
        return $table->getHeader();
    }
    
    public function getFullTable($globalTableIdentifier){
        $splitedId = $this->splitIdentifier($globalTableIdentifier);
        
        $converter = new PhpObjectTableConverter();
        
        $table = $converter->getPhpObjectTable($globalTableIdentifier, $splitedId);
        
        return $table;
    }
    
}

?>
