<?php

/**
 * The global interpreter model. (used when executing a query)
 *
 * @package The-Datatank/universalfilter/interpreter
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jeroen Penninck
 */
interface IInterpreter {
    
    public function findExecuterFor(UniversalFilterNode $filternode);
    
    public function getTableManager();
}

?>
