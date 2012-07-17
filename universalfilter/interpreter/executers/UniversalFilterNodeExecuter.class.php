<?php
/**
 * Top of all executers
 *
 * @package The-Datatank/universalfilter/interpreter/executers
 * @copyright (C) 2012 We Open Data
 * @license AGPLv3
 * @author Jeroen Penninck
 */
abstract class UniversalFilterNodeExecuter {
    
    /**
     * USE AS AN EXPRESSION (Returns a single cell or column)
     */
    
    /**
     * Initializes this node as an expression. It gets the environment of the executer as an argument. 
     */
    public function initExpression(UniversalFilterNode $filter, Environment $topenv, IInterpreter $interpreter){
        //nothing
    }

    /**
     * Returns a single cell or column. => Only the header !!!
     * 
     * It also gets the environment of the executer as an argument. But it should NOT be modified!
     * @return UniversalFilterTableHeader
     */
    public function getExpressionHeader(){
        return null;
    }
    
    /**
     * Returns a single cell or column. => Only the content !!!
     * 
     * It also gets the environment of the executer as an argument. But it should NOT be modified!
     * 
     * @return UniversalFilterTableContent
     */
    public function evaluateAsExpression(){
        return null;
    }
    
    
    
    /**
     * USE AS A FILTER (Returns an environment)
     */
    
    /**
     * Returns a complete environment.
     * @return Environment
     */
    public function execute(UniversalFilterNode $filter, IInterpreter $interpreter) {
        return null;
    }
}