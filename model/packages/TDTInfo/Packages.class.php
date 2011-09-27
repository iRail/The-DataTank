<?php
/**
 * This is a class which will return all the packages in The DataTank
 * 
 * @package The-Datatank/packages/TDTInfo
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@iRail.be>
 */

class Packages extends AReader{

    public static function getParameters(){
	return array();
    }

    public static function getRequiredParameters(){
	return array();
    }

    public function setParameter($key,$val){
    }

    public function read(){
	$resmod = ResourcesModel::getInstance();
        $doc = $resmod->getAllDoc();
	$o = array_keys(get_object_vars($doc));
	return $o;
    }

    public static function getDoc(){
	return "This resource contains every package installed on this DataTank instance.";
    }
}

?>
