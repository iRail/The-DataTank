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
 * @copyright (C) 2012 by iRail vzw/asbl
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

        $packageresourcestring = implode("/",$identifierpieces);

        // The function will throw an exception if a package hasn't been found that matches
        // it will not however throw an exception if no resource has been found.
        $result = $this->resourcesmodel->processPackageResourceString($packageresourcestring);

        if($result["resourcename"] == ""){
            throw new ResourceOrPackageNotFoundTDTException("Illegal identifier. Package does not contain a resourcename: ".$globalTableIdentifier);
        }
        
        return array($result["packagename"],$result["resourcename"],$result["RESTparameters"]);    
    }
    
    private $requestedTables=array();
    
    private function loadTable($globalTableIdentifier){
        $splitedId = $this->splitIdentifier($globalTableIdentifier);
        // TODO: do optimalisation here (if object supports getHeader itself)
        
        $converter = new PhpObjectTableConverter();
        
        $table = $converter->getPhpObjectTable($globalTableIdentifier, $splitedId);
        
        $this->requestedTables[$globalTableIdentifier] = $table;
    }
    
    public function getTableHeader($globalTableIdentifier){
        if(!isset($this->requestedTables[$globalTableIdentifier])){
            $this->loadTable($globalTableIdentifier);
        }
        return $this->requestedTables[$globalTableIdentifier]->getHeader();
    }
    
    public function getTableContent($globalTableIdentifier){
        if(!isset($this->requestedTables[$globalTableIdentifier])){
            $this->loadTable($globalTableIdentifier);
        }
        return $this->requestedTables[$globalTableIdentifier]->getContent();
    }
    
}

?>
