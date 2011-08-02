<?php
/**
 * This class will handle all resources installed in de module directory
 *
 * @package The-Datatank/factories
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Pieter Colpaert
 */

class InstalledResourceFactory extends AResourceFactory{

    public function __construct($module,$resource){
	parent::__construct($module,$resource);
    }

    /**
     * We're overriding this one since it can be done faster
     */
    abstract public function hasResource(){
	return file_exists("modules/" . $this->module . "/" . $this->resource . ".class.php");
    } 

    /**
     * Scans the folder modules for other resources
     * @return an array containing all the resourcenames available
     */
    public static function getAllResourceNames(){
	$modules = array();
	//open the modules directory and loop through it
	if ($handle = opendir('modules/')) {
	    while (false !== ($modu = readdir($handle))) {
		//if the object read is a directory and the configuration methods file exists, then add it to the installed modules
		if ($modu != "." && $modu != ".." && is_dir("modules/" . $modu) && file_exists("modules/" . $modu ."/resources.php")) {
		    include("modules/" . $modu ."/resources.php");
		    $modules[$modu] = $modu::$resources;
		}
	    }
	    closedir($handle);
	}
	return $modules;
    }
    
    
    /**
     * @return gets an instance of a AResource class.
     */
    public function getResource(){
	include_once("modules/" . $this->module . "/" . $this->resource . ".class.php");
	$rn = $this->resource;
	return new $rn();
    }
}

?>
