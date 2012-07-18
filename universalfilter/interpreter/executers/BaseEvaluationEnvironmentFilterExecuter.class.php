<?php

/**
 * Base class for filters that evaluate expressions AND have a source (like select and where)
 * 
 * @package The-Datatank/universalfilter/interpreter/executers
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jeroen Penninck
 */
class BaseEvaluationEnvironmentFilterExecuter extends UniversalFilterNodeExecuter {
    //put your code here
    
    protected function buildChildEnvironment($filter, $topenv, $interpreter, $executer){
        //
        // BUILD ENVIRONMENT TO GIVE TO EXPRESSIONS
        //
        
        //get source environment header
        $executer->initExpression($filter->getSource(), $topenv, $interpreter);
        $header = $executer->getExpressionHeader();
        
        //create new enviroment => combine given table ($topenv) and source table (from executer)
        $giveToColumnsEnvironment = $topenv->newModifiableEnvironment();
        $oldtable = $giveToColumnsEnvironment->getTable();//save old table
        $oldTableRow = new UniversalFilterTableContentRow();
        
        //build new environment
        if(!$oldtable->getHeader()->isSingleRowByConstruction()){
            throw new Exception("Illegal location for columnSelectionFilter");
        }
        for ($oldtablecolumn = 0; $oldtablecolumn < $oldtable->getHeader()->getColumnCount(); $oldtablecolumn++) {
            $columnid = $oldtable->getHeader()->getColumnIdByIndex($oldtablecolumn);
            $column = $oldtable->getHeader()->getColumnInformationById($id);
            $oldtable->getContent()->getRow(0)->copyValueTo($oldTableRow, $columnid, $columnid);
            $giveToColumnsEnvironment->addSingleValue($column, $oldTableRow);
        }
        
        $giveToColumnsEnvironment->setTable(new UniversalFilterTable($header, new UniversalFilterTableContent()));
        
        return $giveToColumnsEnvironment;
    }
}

?>
