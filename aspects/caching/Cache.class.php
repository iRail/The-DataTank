<?php
/**
 * The caching class. Depending on the globals given in Config.class.php it will be able to set and get a variable 
 * 
 * Usage of an implementation:
 *   $c = Cache::getInstance();
 *   $element = $c->get($id);
 *   if(is_null($element)){
 *      $element = get_the_right_data();
 *      $c->set($id,$element,$timeout);
 *   }
 *
 * @package The-Datatank/Cache
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@iRail.be>
 * @author Pieter Colpaert   <pieter@iRail.be>
 * @author Werner Laurensse  <el.lauwer@gmail.com>
 */

include_once("TDTMemCache.class.php");
include_once("TDTNoCache.class.php");

abstract class Cache{
    private static $instance;

    protected function __construct(){
    }

    public static function getInstance(){
        if(!isset(self::$instance)){
            $cacheclass = "TDT".Config::$CACHE_SYSTEM;
            self::$instance = new $cacheclass();
        }
        return self::$instance;
    }
    
    abstract public function set($key, $value, $TTL = 60);
    abstract public function get($key);

}