<?php
/**
 * Implements a filter: ?
 *
 * @package The-Datatank/controllers/spectql/filters
 * @copyright (C) 2011 by OKFN chapter Belgium vzw/asbl
 * @license LGPL
 * @author Pieter Colpaert
 * @organisation Hogent
 */

class SPECTQLFilterList{
    
    private $filter;
    private $addedFilterList;
    private $isAnd;


    public function __construct($filter){
        $this->filter = $filter;
    }

    public function merge($filterList, $and){
        $this->addedFilterList = $filterList;
        $this->isAnd = $and;
    }

    public function execute(&$current){
        if(isset($this->isAnd)){
            if(!$this->isAnd){
                //union
                $result2 = $current; // copy first, we wan to merge afterwards
                $this->addedFilterList->execute($result2);
                $this->filter->execute($current);
                $current = array_merge($current,$result2);
            }else if($this->isAnd){
                //intersection
                $this->filter->execute($current);
                $this->addedFilterList->execute($current);
            }
        }else{
            $this->filter->execute($current);
        }
    }
}

?>