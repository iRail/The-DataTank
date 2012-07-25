<?php

include_once("universalfilter/tablemanager/IUniversalFilterTableManager.interface.php");

include_once("universalfilter/tablemanager/implementation/tools/PhpObjectTableConverter.class.php");

/**
 * This it the implementation of the TableManager for The-DataTank
 * 
 * The TableManager makes it easier to view The DataTank as a collection of tables
 *
 * @package The-Datatank/universalfilter/tablemanager
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jeroen Penninck
 */
class UniversalFilterTableManager implements IUniversalFilterTableManager {
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
     * @param string $globalTableIdentifier => see universal/UniversalFilters.php/Identifier for format
     * @return array of the three pieces
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
        
        $table->getContent()->tableNeeded();//do not destroy content... it's cached...
    }
    
    /**
     * The UniversalInterpreter found a identifier for a table. 
     * Can you give me the header of the table?
     * 
     * @param string $globalTableIdentifier
     * @return UniversalFilterTableHeader 
     */
    public function getTableHeader($globalTableIdentifier){
        if(!isset($this->requestedTables[$globalTableIdentifier])){
            $this->loadTable($globalTableIdentifier);
        }
        return $this->requestedTables[$globalTableIdentifier]->getHeader();
    }
    
    /**
     * The UniversalInterpreter found a identifier for a table. 
     * Can you give me the content of the table?
     * 
     * @param string $globalTableIdentifier
     * @return UniversalTableContent 
     */
    public function getTableContent($globalTableIdentifier){
        if(!isset($this->requestedTables[$globalTableIdentifier])){
            $this->loadTable($globalTableIdentifier);
        }
        return $this->requestedTables[$globalTableIdentifier]->getContent();
    }
    
    
    /**
     * This method makes it possible to run a filter directly on the source.
     * 
     * See documentation of the implemented interface for more information...
     * 
     * @param UniversalFilterNode $query
     * @param string $sourceId
     * @return UniversalFilterNode 
     */
    function runFilterOnSource(UniversalFilterNode $query, $sourceId) {
        if(true){
            return $query;//thereExistNoOptimalisationForThatSource
        }else{
//            execute partial trees on the source with id $sourceId
//                     (or the full query, if it can be converted completely)
//                     (not necessary the case: (even without joins and nested querys)
//                           e.g.: radius() is not a SQL function... etc... 
//                              (bad example because you can convert it...)
//            for each partial anwser the source can calculate {
//                convert it to a table $table
//                replace the calculated node in the query with a new ExternallyCalculatedFilterNode($table, $originalFilterNode, true);
//            }
            return $modifiedQuery;
        }
    }
    
    /**
     * This method is used in combination with runFilterOnSource. 
     * 
     * See documentation of the implemented interface for more information...
     * 
     * @param string $globalTableIdentifier 
     * @return string  A string representing a source.
     */
    public function getSourceIdFromIdentifier($globalTableIdentifier){
        $splited=$this->splitIdentifier($globalTableIdentifier);
        
        return $splited[0].".".$splited[1];
    }
    
}

?>
