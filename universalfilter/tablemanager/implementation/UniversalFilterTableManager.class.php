<?php

include_once("universalfilter/tablemanager/IUniversalFilterTableManager.interface.php");

include_once("universalfilter/tablemanager/implementation/tools/PhpObjectTableConverter.class.php");

include_once("universalfilter/sourcefilterbinding/ExternallyCalculatedFilterNode.class.php");

include_once("universalfilter/data/UniversalFilterTableHeader.class.php");
include_once("universalfilter/data/UniversalFilterTableHeaderColumnInfo.class.php");

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

    private $requestedTableHeaders = array();
    private $requestedTables=array();    

    private $resourcesmodel;
    
    public function __construct() {
        $this->resourcesmodel = ResourcesModel::getInstance();
    }

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
            throw new ResourceOrPackageNotFoundTDTException("Illegal identifier. Package does not contain a resourcename: "
                                                            .$globalTableIdentifier);
        }
        
        return array($result["packagename"],$result["resourcename"],$result["RESTparameters"]);    
    }
    
    private function loadTable($globalTableIdentifier){

        $splitedId = $this->splitIdentifier($globalTableIdentifier);
        
        $converter = new PhpObjectTableConverter();

        $resource = $this->getFullResourcePhpObject($splitedId[0],$splitedId[1]);

        $table = $converter->getPhpObjectTable($splitedId,$resource);
        
        $this->requestedTables[$globalTableIdentifier] = $table;
        
        $table->getContent()->tableNeeded();//do not destroy content... it's cached...
    }

    private function loadTableWithHeader($globalTableIdentifier,$header){

        $splitedId = $this->splitIdentifier($globalTableIdentifier);
        
        $converter = new PhpObjectTableConverter();

        $resource = $this->getFullResourcePhpObject($splitedId[0],$splitedId[1]);

        $table = $converter->getPhpObjectTableWithHeader($splitedId,$resource,$header);
        
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
        
        $model = ResourcesModel::getInstance();
        $identifierpieces = $this->splitIdentifier($globalTableIdentifier);

        $columns = $model->getColumnsFromResource($identifierpieces[0],$identifierpieces[1]);

        if($columns != NULL && !isset($this->requestedTableHeaders[$globalTableIdentifier])){
            $headerColumns = array();
            foreach($columns as $column){
                $nameParts = array();//explode(".",$globalTableIdentifier);
                array_push($nameParts, $column["column_name"]);
                $headerColumn = new UniversalFilterTableHeaderColumnInfo($nameParts);
                array_push($headerColumns,$headerColumn);
            }

            $tableHeader =  new UniversalFilterTableHeader($headerColumns,false,false);
            
            $this->requestedTableHeaders[$globalTableIdentifier] = $tableHeader;
            return $tableHeader;

        }elseif(isset($this->requestedTableHeaders[$globalTableIdentifier])){
            return $this->requestedTableHeaders[$globalTableIdentifier];
        }
        
        
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
     * @param UniversalFilterTableHeader $header The header you created using the above method.
     * @return UniversalTableContent 
     */
    public function getTableContent($globalTableIdentifier, UniversalFilterTableHeader $header){
        if(!isset($this->requestedTables[$globalTableIdentifier])){
            $this->loadTableWithHeader($globalTableIdentifier,$header);
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

        /*
         * Check if resource (source) is queryable
         */
        $model = ResourcesModel::getInstance();

        $globalTableIdentifier = str_replace("/",".",$sourceId);

        $identifierpieces = explode(".",$sourceId);
        array_push($identifierpieces,array());
        $package = $identifierpieces[0];
        $resource = $identifierpieces[1];
        
        // result is FALSE if the resource doesn't implement iFilter
        // result is the resourceObject on which to call and pass the filter upon if it does
        $result = $model->isResourceIFilter($package,$resource);

        if($result == FALSE){
            return $query;//thereExistNoOptimalisationForThatSource
        }else{
//            execute partial trees on the source with id $sourceId
//                     (or the full query, if it can be converted completely)
//                     (not necessary the case: (even without joins and nested querys)
//                           e.g.: radius() is not a SQL function... etc... 
//                              (bad example because you can convert it...)
//            for each partial answer the source can calculate {
//                convert it to a table $table
//                replace the calculated node in the query with a new ExternallyCalculatedFilterNode($table, $originalFilterNode);
//            }
            
            // not only contains the php object of the data but also 
            // the node that has been executed and the index of it in its parent node.
            // if it has done the entire node (so the entire query has been done)
            // only a new Calculated node has to be passed, and $query doesnt have to be
            // adjusted, but replaced.
            // The convention to let this function know if the entire node has been executed is to pass 
            // the index as -1

            // it could also be the case that the filter couldn't do anything with the query
            // this will be clear to this function if the resultObject->phpDataObject = NULL 
            
            $resultObject = $model->readResourceWithFilter($query,$result);
            
            if($resultObject->phpDataObject == NULL){

                return $query;

            }elseif($resultObject->indexInParent == "-1"){

                $converter = new PhpObjectTableConverter();
                $table = $converter->getPhpObjectTable($identifierpieces,$resultObject->phpDataObject);
                return new ExternallyCalculatedFilterNode($table,$query);

            }else{// query has been partially executed
             
                $converter = new PhpObjectTableConverter();
                $table = $converter->getPhpObjectTable($identifierpieces,$resultObject->phpDataObject);
                $replacementNode = new ExternallyCalculatedFilterNode($table,$resultObject->executedNode);
                $parentNode = $resultObject->parentNode;
                $parentNode->setSource($replacementNode,$parentNode->indexInParent);
                return $query;
                
            }
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
