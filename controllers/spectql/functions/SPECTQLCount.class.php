<?php
/**
 * Implements a counting function count(parameterinresource)
 *
 * @package The-Datatank/controllers/spectql/functions
 * @copyright (C) 2012 by OKFN Belgium vzw/asbl
 * @license LGPL
 * @author Pieter Colpaert
 * @organisation FlatTurtle
 */
class SPECTQLCount extends AFunction{

    /**
     * We will select the column from the resource and add it to the specified result. We will always take the distinct values after this operation; Let's not bother with that now.
     */
    public function execute(&$result,&$resourcearray){
        $this->argument->execute($result,$resourcearray);
        //todo ... how will we handle this?
        $name = $this->argument->getName();
        //if it's an array, count it, if it's a value, return 1
        foreach($result as &$row){
            if(isset($row[$name]) && is_array($row[$name])){
                $row[$name] = sizeof($row[$name]);
            }else if(isset($row[$name])){
                $row[$name] = 1;
            }else{
                $row[$name] = 0;
            }
        }
    }
}
?>