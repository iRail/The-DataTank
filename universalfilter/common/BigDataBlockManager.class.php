<?php

/**
 * Keeps blocks of data in memory if possible, but otherwise, writes them to file
 * 
 * Difference with cache: It ALWAYS returns the item if the item is set less than one day ago.
 *  => So no loss of data if cache disabled...
 *
 * @package The-Datatank/universalfilter/interpreter/executers
 * @copyright (C) 2012 We Open Data
 * @license AGPLv3
 * @author Jeroen Penninck
 */
class BigDataBlockManager {
    private static $BLOCKTIMEOUT = 216000;//60*60*60 sec
    private static $instance;
    
    //for now: if no cache: keep in memory
    private $blockarray = array();
    
    public function set($key, $value){
        //IF CACHING ENABLED{
        // SAVE IN CACHE
        //}ELSE{
        $this->blockarray[$key] = $value;
        //}
    }
    
    public function get($key){
        if(isset($this->blockarray[$key])){
            return $this->blockarray[$key];
        }else{
            return null;
        }
    }
    
    public function delete($key){
        unset($this->blockarray[$key]);
    }
    
    /**
     * returns an instance of this class
     * @return BigDataBlockManager
     */
    public static function getInstance(){
        if(!isset(BigDataBlockManager::$instance)){
            BigDataBlockManager::$instance = new BigDataBlockManager();
        }
        return BigDataBlockManager::$instance;
    }
}

?>
