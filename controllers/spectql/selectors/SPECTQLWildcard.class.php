<?php
/**
 * A wildcard in a selector list {*} selects all elements inside a resource.
 *
 * @package The-Datatank/controllers/spectql
 * @copyright (C) 2011 by OKFN chapter Belgium vzw/asbl
 * @license LGPL
 * @author Pieter Colpaert
 * @organisation Hogent
 */

class SPECTQLWildcard extends AArgument{
 
    public function construct(){
        parent::__construct("all");
    }
    
    public function execute(&$current, &$resourcearray){
        //add all the columns in the resourcearray to current
        $current = $resourcearray;
    }

}

?>