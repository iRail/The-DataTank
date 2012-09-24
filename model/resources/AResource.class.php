<?php
/**
 * This is an abstract class that needs to be implemented by any installed resource implementation
 *
 * Adapter design pattern
 * 
 * @package The-Datatank/resources
 * @license AGPLv3
 * @author Pieter Colpaert   <pieter@iRail.be>
 * @author Jan Vansteenlandt <jan@iRail.be>
 * @author Hannes Van De Vreken <hannes@iRail.be>
 */

include_once("model/resources/read/AReader.class.php");
abstract class AResource extends AReader{

    public function read(){
        return $this->call();
    }
    
    public static function getParameters() {
        return array();
    }
    
    public static function getRequiredParameters() {
        return array();
    }
    
    protected function setParameter($key, $value){
        return $this ;
    }
    
    abstract public static function getDoc();
}
?>
