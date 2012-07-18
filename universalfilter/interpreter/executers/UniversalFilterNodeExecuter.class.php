<?php
/**
 * Base class of all executers
 *
 * @package The-Datatank/universalfilter/interpreter/executers
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jeroen Penninck
 */
abstract class UniversalFilterNodeExecuter {
    
    /**
     * Initializes this node as an expression. It gets the environment of the executer as an argument. 
     * @param UniversalFilterNode $filter The corresponding filter
     * @param Environment $topenv The environment given to evaluate this filter. It should NEVER be modified.
     * @param IInterpreter $interpreter The interpreter that evaluates this tree.
     */
    public function initExpression(UniversalFilterNode $filter, Environment $topenv, IInterpreter $interpreter){
        //nothing
    }

    /**
     * Returns the header of the returned table
     * 
     * @return UniversalFilterTableHeader
     */
    public function getExpressionHeader(){
        return null;
    }
    
    /**
     * Calculates and returns the content of the table
     * 
     * @return UniversalFilterTableContent
     */
    public function evaluateAsExpression(){
        return null;
    }
}