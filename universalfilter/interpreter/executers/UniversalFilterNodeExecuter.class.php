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
     * What sources are used by this node or by the nodes this node uses.
     * 
     * @return SourceUsageData 
     */
    public function getCombinedSourceUsage(){return null;}
    
    /**
     * Get the sources of this executer which can be executed without knowledge of the rest of the query.
     * 
     * Note that: AVG(someColumnName) returns no items, while AVG(SELECT ... FROM ...) returns its source (if it does not contain a columnName defined in the rest of the query).
     * 
     * @return array of UniversalFilterExecuters
     */
    public function getNonDependingSourceExecuters(){
        return array();
    }
    
    /**
     * Calculates and returns the content of the table
     * 
     * @return UniversalFilterTableContent
     */
    public abstract function evaluateAsExpression();
}