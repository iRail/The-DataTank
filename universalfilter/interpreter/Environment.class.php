<?php

/**
 * Description of Environment
 *
 * @author Jeroen
 */
class Environment {
    //
    // Identifier should be able to ask: 
    //  - what is the full table name of this alias?
    //  - can you give me the table with full name ... ?
    //  - give me the column with name ...
    //  - give me a cell with name ...
    //  
    //  => Aliases and TableManager and other Tables should be kept inside Environment
    //
    
    private $aliasses=array();
    
    private $tablemanager;
    
    private $tables=array();
    private $lasttable;
    private $tableint;
    
    public function __construct(UniversalFilterTableManager $tablemanager) {
        $this->tablemanager = $tablemanager;
        $lasttable = -1;
    }


    public function getTableManager(){
        return $this->tablemanager;
    }
    
    
    /**
     * manage aliases
     */
    public function aliasLastTable($aliasname){
        $this->aliasses[$aliasname]=$lasttable;
    }
    
    public function clearAliases(){
        $this->aliasses=array();
    }
    
    
    /**
     * Manage tables
     */
    
    /**
     * add a table
     */
    public function addTable(UniversalFilterTable $table) {
        $this->tableint++;
        $this->lasttable=$this->tableint;
        $this->tables[$this->lasttable]=$table;
    }
    
    public function replaceCurrentTable(UniversalFilterTable $table){
        $this->tables[$this->lasttable]=$table;
        $this->clearColumnContentCache();
    }
    
    /**
     * get the last added table
     */
    public function getLastTable(){
        return $this->tables[$this->lasttable];
    }
    
    public function setTableAliasCurrent($alias) {
        $this->lasttable = $aliasnames[$alias];
        $this->columnName = null;//no column
        //TODO: clear cache if alias already in tables
        return $name;
    }
    
    private function getAliasTable($tablealias){
        if(isset($this->aliasses[$tablealias])){
            return $this->tables[$this->aliasses[$tablealias]];
        }else{
            return $this->getLastTable();
        }
    }
    
    private $columnHeaderCache=array();
    /**
     * Get a column from the data (header)
     */
    public function getColumnDataHeader($tablealias, $columnName){//get a single column from the table
        if(!isset($this->columnHeaderCache[$tablealias.".".$columnName])){

            $table=$this->getAliasTable($tablealias);

            $oldheader = $table->getHeader();

            if(!in_array($columnName, $oldheader->getColumnNames())){
                throw new Exception("Illegal column request. No column with id \"$columnName\".");
            }
            $newColumnNames=array($columnName);
            $newLinks=array();
            if($oldheader->isLinkedColumn($columnName)){
                $newLinks=array($columnName=>array(
                    "table"=>$oldheader->getLinkedTable($columnName), 
                    "key"=>$oldheader->getLinkedTableKey($columnName)));//$oldheader
            }

            $columnHeader = new UniversalFilterTableHeader(array($columnName), $newLinks, $oldheader->isSingleRowByConstruction(), true);
            $this->columnHeaderCache[$tablealias.".".$columnName]=$columnHeader;
            return $columnHeader;
        }else{
            return $this->columnHeaderCache[$tablealias.".".$columnName];
        }
    }
    
    private $columnContentCache=array();
    
    /**
     * Get a column from the data (content)
     */
    public function getColumnDataContent($tablealias, $columnName){//get a single column from the table
        if(!isset($this->columnContentCache[$tablealias.".".$columnName])){
            $table=$this->getAliasTable($tablealias);

            $oldheader = $table->getHeader();

            if(!in_array($columnName, $oldheader->getColumnNames())){
                throw new Exception("Illegal column request. No column with id \"".$this->columnName."\".");
            }

            $oldRows=$table->getContent()->getRows();
            $rows=array();
            foreach($oldRows as $index => $row){
                $newRow=new UniversalFilterTableContentRow();
                $newRow->defineValue($columnName, $row->getValue($columnName));
                $rows[$index] = $newRow;
            }

            $columnContent = new UniversalFilterTableContent($rows);
            $this->columnContentCache[$tablealias.".".$columnName]=$columnContent;
            return $columnContent;
        }else{
            return $this->columnContentCache[$tablealias.".".$columnName];
        }
    }
    
    public function clearColumnContentCache(){
        $this->columnContentCache=array();
    }

    /**
     * Clone Environment
     */
    public function newModifiableEnvironment(){
        $newEnv=new Environment($this->tablemanager);
        $newEnv->setData($this->aliasses, $this->tables, $this->lasttable, $this->tableint);
        return $newEnv;
    }
    
    protected function setData($aliasses, $tables, $lasttable, $tableint){
        $this->aliasses = $aliasses;//copy
        $this->tables = $tables;//copy array, not tables (those should not be modified)
        $this->lasttable = $lasttable;//copy string
        $this->tableint = $tableint;
    }
}

?>
