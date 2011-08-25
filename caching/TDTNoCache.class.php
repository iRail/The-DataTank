<?php
/**
 * Dummy class - when no cache could be installed on the system (e.g. cheap hosts)
 * 
 * @package The-Datatank/cache
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@thedatatank.com>
 * @author Pieter Colpaert   <pieter@thedatatank.com>
 * @author Werner Laurensse  <werner@thedatatank.com>
 */

class TDTNoCache extends Cache{
    protected function __construct(){
        
    }

    public function set($key,$value, $timeout=60){
        //do nothing
    }

    public function get($key){
        return null;
    }
}