<?php

/**
 * Executes an Identifier
 *
 * format: 
 * - package.package.resource
 * - package.package.resource.name_of_column
 * - alias.name_of_column
 *
 * @package The-Datatank/universalfilter/interpreter/executers
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jeroen Penninck
 */
class IdentifierExecuter extends UniversalFilterNodeExecuter {
    // TODO: error handling
    
    private $filter;
    private $header;
    private $topenv;
    
    public function initExpression(UniversalFilterNode $filter, Environment $topenv, IInterpreter $interpreter) {
        $this->filter = $filter;
        $this->topenv = $topenv;
        
        $this->header = $topenv->getColumnDataHeader($this->filter->getIdentifierString());
    }
    
    // Evaluate Column/Cell
    public function getExpressionHeader(){
        return $this->header;
    }
    
    public function evaluateAsExpression() {
        return $this->topenv->getColumnDataContent($this->filter->getIdentifierString(), $this->header);
    }
    
    /**
     * Gets the table from the tablemanager
     * And creates a new Environment with only this table.
     */
    public function execute(UniversalFilterNode $filter, IInterpreter $interpreter) {
        $newEnv = new Environment();
        $tableName = $filter->getIdentifierString();
        
        $newEnv->setTable($interpreter->getTableManager()->getFullTable($tableName));//TODO: error handling
        //echo "resource(table) => $tableName<br/>";
        
        return $newEnv;
    }
}

?>
