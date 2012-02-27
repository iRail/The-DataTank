<?php
/**
 * Implements a simple column name to select { ... , column }
 *
 * @package The-Datatank/controllers/spectql/selectors
 * @copyright (C) 2011 by OKFN chapter Belgium vzw/asbl
 * @license LGPL
 * @author Pieter Colpaert
 * @organisation Hogent
 */
class SPECTQLColumnName extends AArgument{

    /**
     * We will select the column from the resource and add it to the specified result. We will always take the distinct values after this operation; Let's not bother with that now.
     */
    public function execute(&$result,&$resourcearray){
        $name = $this->name;
        $i = 0;
        foreach($resourcearray as $row){
            if(isset($row[$name])){
                $result[$i][$name] = $row[$name];
            }
            $i++;
        }
    }
}
?>