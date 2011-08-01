<?php
/**
 * The abstract class for a factory: check documentation on the Factory Method Pattern if you don't understand this code.
 *
 * @package The-Datatank/resources
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert
 */

class GenericResource extends AResource {

    public static function getRequiredParameters(){
	return array(); //TODO get from db
    }

    public static function getParameters(){
	return array();
    }
     
    public static function getAllowedPrintMethods(){
	return array();
    }

    public static function getDoc(){
	echo "I'm undocumented Q_Q";
    }

    public function call(){
	
    }    

    public function setParameter($name,$val){
	
    }

}

?>