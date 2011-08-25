<?php
/**
 * MemCache implementation of Cache
 * 
 * @package The-Datatank
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@iRail.be>
 * @author Pieter Colpaert   <pieter@iRail.be>
 * @author Werner Laurensse  <el.lauwer@gmail.com>
 */

class TDTMemCache extends Cache{
    private $memcache;
    
    protected function __construct(){
        $this->memcache = new Memcache();
        /*
         * This is something tricky in PHP. If you use pconnect (p=persistent) the connection will remain open all the time.
         * This is not a bad thing since we're using the cache all the time (you don't turn off the light of the kitchen if you're running in and out, switching it too much would even consume more)
         * In memcache, the old but stable implementation in PHP of memcached the persistent connection works like charm
         * In memcached however there is a severe bug which leads to a memory leak. If you'd take over code from this class to implement memcached, DON'T use the persistent connect!
         */
        if(!$this->memcache->pconnect(Config::$CACHE_HOST, Config::$CACHE_PORT)){
            throw new CacheException("could not connect");
        }
    }

    public function set($key,$value,$timeout=60){
        $this->memcache->set($key, $value, FALSE, $timeout); //the true flag will compress the value using zlib
    }

    public function get($key){
        if($this->memcache->get($key)){
            return $this->memcache->get($key);
        }
        return null;
    }
    
}