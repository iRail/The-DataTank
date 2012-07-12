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
    // TODO: TableManager uit Environment gooien !!! 
    
    // Evaluate Column/Cell
    public function evaluateAsExpressionHeader(UniversalFilterNode $filter, Environment $topenv, IInterpreter $interpreter){
        //skip of the last part of the identifier and
        $parts = explode(".", $filter->getIdentifierString());
        $columnId = array_pop($parts);
        $alias=implode(".",$parts);
        
        return $topenv->getColumnDataHeader($alias, $columnId);
    }
    
    public function evaluateAsExpression(UniversalFilterNode $filter, Environment $topenv, IInterpreter $interpreter) {
        //skip of the last part of the identifier and
        $parts = explode(".", $filter->getIdentifierString());
        $columnId = array_pop($parts);
        $alias=implode(".",$parts);
        
        return $topenv->getColumnDataContent($alias, $columnId);
    }
    
    /**
     * Gets the table from the tablemanager
     * And creates a new Environment with only this table.
     */
    public function execute(UniversalFilterNode $filter, IInterpreter $interpreter) {
        $newEnv = new Environment($interpreter->getTableManager());
        $tableName = $filter->getIdentifierString();
        
        $newEnv->addTable($interpreter->getTableManager()->getFullTable($tableName));//TODO: error handling
        //echo "resource(table) => $tableName<br/>";
        return $newEnv;
    }
}

?>
