<?php
/**
 * Top of all executers
 *
 * @package The-Datatank/universalfilter/interpreter/executers
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jeroen Penninck
 */
abstract class UniversalFilterNodeExecuter {

    /**
     * Returns a single cell or column. => Only the header !!!
     * 
     * It also gets the environment of the executer as an argument. But it should NOT be modified!
     */
    public function evaluateAsExpressionHeader(UniversalFilterNode $filter, Environment $topenv, IInterpreter $interpreter){
        return null;
    }
    
    /**
     * Returns a single cell or column. => Only the content !!!
     * 
     * It also gets the environment of the executer as an argument. But it should NOT be modified!
     */
    public function evaluateAsExpression(UniversalFilterNode $filter, Environment $topenv, IInterpreter $interpreter) {
        return null;
    }
    
    /**
     * Returns a complete environment.
     */
    public function execute(UniversalFilterNode $filter, IInterpreter $interpreter) {
        return null;
    }
}