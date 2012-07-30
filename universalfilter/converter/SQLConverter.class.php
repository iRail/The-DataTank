<?php
/**
 * This class converts a tree to a SQL query string (simple query) 
 * This is based on the debugging TreeToString of the universalfilters
 *
 * IMPORTANT NOTE: the functions which contain "not supported yet" are meant for this converter
 * this doesnt mean that the functionality hasn't been implemented in the universalinterpreter!
 *
 * @package The-Datatank/universalfilter/tools
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt
 */

include_once("universalfilter/UniversalFilters.php");

class SQLConverter{
    

    private $sql = "";

    // the SELECT identifiers
    private $identifiers = array();
    private $IN_SELECT_CLAUSE = TRUE;
    private $headerNames;
    

    public function __construct($headerNames){
        $this->headerNames = $headerNames;
    }
    

    /**
     * Converts a UniversalFilterNode to a string you can print...
     * @param UniversalFilterNode $tree
     * @return string A string representation of the tree
     */
    public function treeToSQL(UniversalFilterNode $tree){
        $method = "print_".get_class($tree);
        //calls the correct clone method and then returns.
        $this->$method($tree);
        return $this->sql;
    }
    
    private function print_Identifier(Identifier $filter){
        // just add it to the string
        $this->sql.= $filter->getIdentifierString(). " ";
        
        if($this->IN_SELECT_CLAUSE){
            array_push($this->identifiers,$filter->getIdentifierString());
        }
        
    }

    public function getIdentifiers(){
        return $this->identifiers;
        
    }

    private function print_Constant(Constant $filter){
        // just add it to the string
        $this->sql.= $filter->getConstant(). " ";
    }
    
    private function print_TableAliasFilter(TableAliasFilter $filter){
        // not implemented yet
    }
    
    private function print_FilterByExpressionFilter(FilterByExpressionFilter $filter){

        // add a WHERE clause the source is to be added in the FROM
        $this->sql.= " FROM " . $filter->getSource()->getIdentifierString() . " ";
        $this->sql.= "WHERE ";
        $this->IN_SELECT_CLAUSE = FALSE;
        $this->treeToSQL($filter->getExpression());
                
    }
    
    private function print_ColumnSelectionFilter(ColumnSelectionFilter $filter){

        $this->sql.= "SELECT ";
        
        foreach ($filter->getColumnData() as $index => $originalColumn) {
            $this->treeToSQL($originalColumn->getColumn());

            // insert requiredHeaderName !!
            $headerName = array_shift($this->headerNames);
            $this->sql.= "AS $headerName";
            $this->sql.= ", ";
        }
        
        $this->sql = rtrim($this->sql, ", ");
        
        if($filter->getSource()->getType() == "IDENTIFIER"){
            $this->sql.= " FROM " . $filter->getSource()->getIdentifierString();
        }else{
            $this->treeToSQL($filter->getSource());
        }
    }
    
    private function print_DistinctFilter(DistinctFilter $filter){
        // not supported yet
    }
    
    private function print_DataGrouper(DataGrouper $filter){
        // not supported yet
        
    }
    
    private function print_UnairyFunction(UnairyFunction $filter){
        // map the types on the correct functions like FUNCTION_UNARY_UPPERCASE -> uppercase()
        // maybe the default should be mysql syntax in case different engines support
        // different unairyfunction grammatics.
        // NOT SUPPORTED IN THIS SIMPLE CONVERTER

        switch($filter->getType()){
            case UnairyFunction::$FUNCTION_UNAIRY_UPPERCASE:
                $this->sql.= "UPPER( ";
                $this->treeToSQL($filter->getSource(0));
                $this->sql.= " ) ";
                break;
            case UnairyFunction::$FUNCTION_UNAIRY_LOWERCASE:
                $this->sql.= "LOWER( ";
                $this->treeToSQL($filter->getSource(0));
                $this->sql.= " ) ";
                break;
            case UnairyFunction::$FUNCTION_UNAIRY_STRINGLENGTH:
                $this->sql.= "LEN( ";
                $this->treeToSQL($filter->getSource(0));
                $this->sql.= " ) ";
                break;
            case UnairyFunction::$FUNCTION_UNAIRY_ROUND:
                $this->sql.= "ROUND( ";
                $this->treeToSQL($filter->getSource(0));
                $this->sql.= " ) ";
                break;
            case UnairyFunction::$FUNCTION_UNAIRY_ISNULL:
                $this->sql.= "ISNULL( ";
                $this->treeToSQL($filter->getSource(0));
                $this->sql.= " ) ";
                break;
            default:
                break;
        }
    }
    
    private function print_BinaryFunction(BinaryFunction $filter){
        // note: we don't support every function! This is just an example SQLConverter

        switch ($filter->getType()) {
            case BinaryFunction::$FUNCTION_BINARY_COMPARE_EQUAL:
                $this->treeToSQL($filter->getSource(0));
                $this->sql.= "=";
                $this->treeToSQL($filter->getSource(1));
                break;
            case BinaryFunction::$FUNCTION_BINARY_COMPARE_SMALLER_THAN:
                $this->treeToSQL($filter->getSource(0));
                $this->sql.= "<";
                $this->treeToSQL($filter->getSource(1));
                break;
            case BinaryFunction::$FUNCTION_BINARY_COMPARE_LARGER_THAN:
               $this->treeToSQL($filter->getSource(0));
               $this->sql.= ">";
               $this->treeToSQL($filter->getSource(1));
               break;
            case BinaryFunction::$FUNCTION_BINARY_COMPARE_LARGER_OR_EQUAL_THAN:
               $this->treeToSQL($filter->getSource(0));
               $this->sql.= ">=";
               $this->treeToSQL($filter->getSource(1));
               break;          
            case BinaryFunction::$FUNCTION_BINARY_COMPARE_SMALLER_OR_EQUAL_THAN:
               $this->treeToSQL($filter->getSource(0));
               $this->sql.= "<=";
               $this->treeToSQL($filter->getSource(1));
               break;
            case BinaryFunction::$FUNCTION_BINARY_COMPARE_NOTEQUAL:
               $this->treeToSQL($filter->getSource(0));
               $this->sql.= "!=";
               $this->treeToSQL($filter->getSource(1));
               break;
            case BinaryFunction::$FUNCTION_BINARY_AND:
               $this->treeToSQL($filter->getSource(0));
               $this->sql.= " AND ";
               $this->treeToSQL($filter->getSource(1));
               break;
            case BinaryFunction::$FUNCTION_BINARY_OR:
               $this->treeToSQL($filter->getSource(0));
               $this->sql.= " OR ";
               $this->treeToSQL($filter->getSource(1));
               break;
            default:
                break;
        }
    }
    
    private function print_TertairyFunction(TertairyFunction $filter){
        // not supported yet
    }
    
    private function print_AggregatorFunction(AggregatorFunction $filter){
        // not supported yet
    }
    
    private function print_CheckInFunction(CheckInFunction $filter){
        // not supported yet
        
    }
    
}

?>