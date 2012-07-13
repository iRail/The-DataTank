<?php

/**
 * A universal representation of a table
 *
 * @package The-Datatank/universalfilter/data
 * @copyright (C) 2012 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jeroen Penninck
 */
class UniversalFilterTable {
    private $header;
    private $content;

    public function __construct($header, $content) {
        $this->header=$header;
        $this->content=$content;
    }
    
    public function getHeader(){
        return $this->header;
    }
    
    public function getContent(){
        return $this->content;
    }
    
    public function setHeader(UniversalFilterTableHeader $header){
        $this->header = $header;
    }
    
    public function setContent(UniversalFilterTableContent $content){
        $this->content = $content;
    }
}
?>
