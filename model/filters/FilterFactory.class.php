<?php
  /**
   * This factory will provide Filters such as RESTFilter and SearchFilter
   *
   * @package The-Datatank/model/filters
   * @copyright (C) 2011 by iRail vzw/asbl
   * @license AGPLv3
   * @author Jan Vansteenlandt
   */
include_once("model/filters/AFilter.class.php");

class FilterFactory{
    
    private static $factory;
    
    private function __construct(){

    }
    
    public static function getInstance(){
	if(!isset(self::$factory)){
	    self::$factory = new FilterFactory();
	}
	return self::$factory;
    }

    public static function getFilter($filter,$params){
	include_once("model/filters/$filter.class.php");
	return new $filter($params);
    }
    
}
?>
