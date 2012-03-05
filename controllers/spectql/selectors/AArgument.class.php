<?php
/**
 * Abstract item for an item in a selector: {...}
 *
 * @package The-Datatank/controllers/spectql/selectors
 * @copyright (C) 2011 by OKFN chapter Belgium vzw/asbl
 * @license LGPL
 * @author Pieter Colpaert
 * @organisation Hogent
 */
abstract class AArgument{
    
    protected $name, $alias;
    
    public function __construct($name, $alias = ""){
        $this->name = $name;
        $this->alias = $alias;
    }
    
    /**
     * Executes the argument. This execute function does not return anything
     * @param $current first parameter is the result array that we've produced so far. You can change this array as it is passed by reference.
     * @param $resourcearray contains all the data
     */
    abstract public function execute(&$current, &$resourcearray);

    /**
     * Gets the name of a certain argument
     * @return name of the column we're going to produce
     */
    public function getName(){
        return $this->name;
    }

    public function getColumnName(){
        return $this->alias == "" ? $this->name:$this->alias;
    }
    
    
}



?>
