<?php
/**
 * Limits a resource
 *
 * @package The-Datatank/controllers/spectql/functions
 * @copyright (C) 2011 by OKFN chapter Belgium vzw/asbl
 * @license LGPL
 * @author Pieter Colpaert
 * @organisation Hogent
 */
class SPECTQLLimit extends AFunction{

    /**
     * We will select the column from the resource and add it to the specified result. We will always take the distinct values after this operation; Let's not bother with that now.
     */
    public function execute(&$result,&$resourcearray){
        $argument= $this->argument;
        if(!is_numeric($this->argument)){
            $argument = $this->argument->execute($result,$resourcearray);
        }
        if(is_array($result)){
            $total = count($result);
            for($i = $argument; $i < $total;$i++){
                unset($result[$i]);
            }
        }
    }
}
?>