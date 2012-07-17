<?php

/**
 * The global interpreter model. (used when executing a query)
 *
 * @package The-Datatank/universalfilter/interpreter
 * @copyright (C) 2012 We Open Data
 * @license AGPLv3
 * @author Jeroen Penninck
 */
interface IInterpreter {
    
    /**
     * @return UniversalFilterNodeExecuter
     */
    public function findExecuterFor(UniversalFilterNode $filternode);
    
    /**
     * @return UniversalFilterTableManager
     */
    public function getTableManager();
}

?>
