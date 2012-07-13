<?php


/**
 * Top class of all expressions
 * 
 * @package The-Datatank/universalfilter/interpreter/executers
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jeroen Penninck
 */
abstract class ExpressionNodeExecuter extends UniversalFilterNodeExecuter {
    
    public function execute(UniversalFilterNode $filter, IInterpreter $interpreter) {
        throw new Exception("Can not execute binary function!");
    }
}
?>