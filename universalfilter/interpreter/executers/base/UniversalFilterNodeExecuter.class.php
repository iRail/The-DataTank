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
     * Initializes this node. It gets the environment of the executer as an argument. 
     * @param UniversalFilterNode $filter The corresponding filter
     * @param Environment $topenv The environment given to evaluate this filter. It should NEVER be modified.
     * @param IInterpreterControl $interpreter The interpreter that evaluates this tree.
     * @param bool $preferColumn Does the parent expression would like me to give back a column?
     */
    public abstract function initExpression(UniversalFilterNode $filter, Environment $topenv, IInterpreterControl $interpreter, $preferColumn);

    /**
     * Returns the header of the returned table
     * 
     * @return UniversalFilterTableHeader
     */
    public abstract function getExpressionHeader();
    
    /**
     * Calculates and returns the content of the table
     * 
     * @return UniversalFilterTableContent
     */
    public abstract function evaluateAsExpression();
    
    /**
     * Cleanup
     */
    public function cleanUp(){}
    
    /**
     * Finds out which sources this executer uses, 
     * and which parts of the query can be executed on one source
     * 
     * @param UniversalFilterNode $filter The corresponding filter
     * @param SourceUsageEnvironment $topenv The environment given to evaluate this filter. It should NEVER be modified.
     * @param IInterpreterControl $interpreter The interpreter that evaluates this tree.
     * @return array of SourceUsageData
     */
    public function filterSingleSourceUsages(UniversalFilterNode $filter, SourceUsageEnvironment $topenv, IInterpreterControl $interpreter){
        return array();
    }
}