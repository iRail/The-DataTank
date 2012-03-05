<?php
/**
 * Abstract class for a function. It is in fact a meta-argument as it wraps around an argument
 *
 * @package The-Datatank/controllers/spectql/functions
 * @copyright (C) 2011 by OKFN chapter Belgium vzw/asbl
 * @license LGPL
 * @author Pieter Colpaert
 * @organisation Hogent
 */
abstract class AFunction  extends AArgument{
    
    protected $argument;
    
    public function __construct($name, $argument, $alias = ""){
        parent::__construct($name,$alias);
        $this->argument = $argument;
    }
    
    /**
     * Gets the argument given with the function
     * @return the argument we need to execute in order to start the thing
     */
    public function getArgument(){
        return $this->argument;
    }

    public function getName(){
        return $this->name . "_" . $this->argument->getName();
    }
    
    
}
?>