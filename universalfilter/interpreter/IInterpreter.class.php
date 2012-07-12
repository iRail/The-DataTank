<?php

/**
 * Description of Interpreter
 *
 * @author Jeroen
 */
interface IInterpreter {
    
    public function findExecuterFor(UniversalFilterNode $filternode);
    
    public function getTableManager();
}

?>
