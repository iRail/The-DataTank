<?php
/**
 * All possible exceptions while parsing are defined here
 *
 * @package The-Datatank/controllers
 * @copyright (C) 2011 by OKFN chapter Belgium vzw/asbl
 * @license LGPL
 * @author Pieter Colpaert
 * @organisation Hogent
 */

include_once("aspects/errors/AbstractTDTException.class.php");

class FunctionDoesNotExistTDTException extends AbstractTDTException{
    public static function getErrorCode(){
        return 480;
    }
    
    public static function getDoc(){
        return "Thrown when a certain token in your query cannot be parsed. This function does not exist.";
    }

    public function __construct($string){
        parent::__construct("Could not process this token: " . $string, self::getErrorCode());
    }
    
}
class ParserTDTException extends AbstractTDTException{
    public static function getErrorCode(){
        return 481;
    }
    
    public static function getDoc(){
        return "General parser problem";
    }

    public function __construct($string){
        parent::__construct("Could not process this token: " . $string, self::getErrorCode());
    }
    
}
class KeyDoesNotExistTDTException extends AbstractTDTException{
    public static function getErrorCode(){
        return 482;
    }
    
    public static function getDoc(){
        return "Key does not exist";
    }

    public function __construct($string){
        parent::__construct("Could not get this key: " . $string . " - This means it probably does not exist any longer or has never existed in the dataset.", self::getErrorCode());
    }
    
}